<?php

use infuse\Config;
use infuse\Database;
use infuse\ErrorStack;
use infuse\Locale;
use infuse\Model;
use infuse\Response;
use infuse\Request;
use infuse\Router;
use infuse\Util;
use infuse\Validate;
use infuse\ViewEngine;
use infuse\Queue;
use infuse\Session;
use Monolog\ErrorHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Logger;
use Pimple\Container;

if( !defined( 'INFUSE_BASE_DIR' ) )
	die( 'INFUSE_BASE_DIR has not been defined!' );

/* app constants */
if( !defined( 'INFUSE_APP_DIR' ) )
	define( 'INFUSE_APP_DIR', INFUSE_BASE_DIR . '/app' );
if( !defined( 'INFUSE_ASSETS_DIR' ) )
	define( 'INFUSE_ASSETS_DIR', INFUSE_BASE_DIR . '/assets' );
if( !defined( 'INFUSE_PUBLIC_DIR' ) )
	define( 'INFUSE_PUBLIC_DIR', INFUSE_BASE_DIR . '/public' );
if( !defined( 'INFUSE_TEMP_DIR' ) )
	define( 'INFUSE_TEMP_DIR', INFUSE_BASE_DIR . '/temp' );
if( !defined( 'INFUSE_VIEWS_DIR' ) )
	define( 'INFUSE_VIEWS_DIR', INFUSE_BASE_DIR . '/views' );

/* error codes */
define( 'ERROR_NO_PERMISSION', 'no_permission' );
define( 'VALIDATION_FAILED', 'validation_failed' );
define( 'VALIDATION_REQUIRED_FIELD_MISSING', 'required_field_missing' );
define( 'VALIDATION_NOT_UNIQUE', 'not_unique' );

/* useful constants */
define( 'SKIP_ROUTE', -1 );

class App extends Container
{
	function __construct( array $configValues = [] )
	{
		parent::__construct();

		$app = $this;

		/* Load Configuration */

		$this[ 'config' ] = function() use ( $configValues ) {
			return new Config( $configValues );
		};

		$config = $app[ 'config' ];

		/* Logging */

		$this[ 'logger' ] = function() use ( $app, $config ) {
			$processors = [
				new WebProcessor,
				new IntrospectionProcessor ];

			$handlers = [ new ErrorLogHandler ];

			// firephp
			if( !$config->get( 'site.production-level' ) )
				$handlers[] = new FirePHPHandler;

			return new Logger( $config->get( 'site.host-name' ), $handlers, $processors );
		};

		/* Error Reporting */

		ini_set( 'display_errors', $config->get( 'site.production-level' ) );
		ini_set( 'log_errors', 1 );
		error_reporting( E_ALL | E_STRICT );

		ErrorHandler::register( $this[ 'logger' ] );

		/* Time Zone */

		if( $tz = $config->get( 'site.time-zone' ) )
			date_default_timezone_set( $tz );

		/* Constants */

		if( !defined( 'SITE_TITLE' ) )
			define( 'SITE_TITLE', $config->get( 'site.title' ) );

		/* Locale */

		$this[ 'locale' ] = function() use ( $app, $config ) {
			$locale = new Locale( $config->get( 'site.language' ) );
			$locale->setLocaleDataDir( INFUSE_ASSETS_DIR . '/locales' );
			return $locale;
		};

		/* Validator */

		Validate::configure( [ 'salt' => $config->get( 'site.salt' ) ] );

		/* Database */

		$dbSettings = (array)$config->get( 'database' );
		$dbSettings[ 'productionLevel' ] = $config->get( 'site.production-level' );
		Database::configure( $dbSettings );
		Database::inject( $this );

		/* Redis */

		$redisConfig = $config->get( 'redis' );
		if( $redisConfig )
		{
			$this[ 'redis' ] = function() use ( $app, $redisConfig ) {
				return new Predis\Client( $redisConfig );
			};
		}

		/* Memcache */

		$memcacheConfig = $config->get( 'memcache' );
		if( $memcacheConfig )
		{
			$this[ 'memcache' ] = function() use ( $app, $memcacheConfig ) {
				$memcache = new Memcache;
				$memcache->connect( $memcacheConfig[ 'host' ], $memcacheConfig[ 'port' ] );
				return $memcache;
			};
		}

		/* Request + Response */

		$this[ 'req' ] = function() use ( $app ) {
			return new Request();
		};

		$this[ 'res' ] = function() use ( $app ) {
			return new Response( $app );
		};

		$req = $this[ 'req' ];
		$res = $this[ 'res' ];

		/* Queue */

		$this[ 'queue' ] = function() use ( $app, $config ) {
			Queue::configure( array_merge( [
				'namespace' => '\\app' ], (array)$config->get( 'queue' ) ) );

			Queue::inject( $app );

			return new Queue( $config->get( 'queue.type' ), (array)$config->get( 'queue.listeners' ) );
		};

		/* Ironmq */

		$this[ 'ironmq' ] = function() use ( $app, $config ) {
			return new IronMQ( [
				'token' => $config->get( 'queue.token' ),
				'project_id' => $config->get( 'queue.project' ) ] );
		};

		/* Models */

		Model::inject( $this );
		Model::configure( (array)$config->get( 'models' ) );

		/* Error Stack */

		$this[ 'errors' ] = function() use ( $app ) {
			return new ErrorStack( $app );
		};

		/* ViewEngine  */

		$this[ 'base_url' ] = (($config->get('site.ssl-enabled'))?'https':'http') . '://' . $config->get('site.host-name') . '/';

		$this[ 'view_engine' ] = function() use ( $app, $config ) {
			$engine = new ViewEngine( [
				'engine' => $config->get( 'views.engine' ),
				'viewsDir' => INFUSE_VIEWS_DIR,
				'compileDir' => INFUSE_TEMP_DIR . '/smarty',
				'cacheDir' => INFUSE_TEMP_DIR . '/smarty/cache',
				'assetMapFile' => INFUSE_ASSETS_DIR . '/static.assets.json',
				'assetsBaseUrl' => $config->get( 'assets.base_url' ) ] );
			$engine->assignData( [ 'app' => $app ] );

			return $engine;
		};

		$config->set( 'assets.dirs', [ INFUSE_PUBLIC_DIR ] );

		/* Session */

		$sessionAdapter = $config->get( 'session.adapter' );
		if( !$req->isApi() && $sessionAdapter )
		{
			// initialize sessions
			ini_set( 'session.use_trans_sid', false );
			ini_set( 'session.use_only_cookies', true ); 
			ini_set( 'url_rewriter.tags', '' );
			ini_set( 'session.gc_maxlifetime', $config->get( 'session.lifetime' ) );

			// set the session name
			$sessionTitle = $config->get( 'site.title' ) . '-' . $req->host();
			$safeSessionTitle = str_replace( [ '.',' ',"'", '"' ], [ '','_','','' ], $sessionTitle );
			session_name( $safeSessionTitle );
			
			// set the session cookie parameters
			session_set_cookie_params(
			    $config->get( 'session.lifetime' ), // lifetime
			    '/', // path
			    '.' . $req->host(), // domain
			    $req->isSecure(), // secure
			    true // http only
			);

			if( $sessionAdapter == 'redis' )
				Session\Redis::start( $app, $config->get( 'session.prefix' ) );
			else if( $sessionAdapter == 'database' )
				Session\Database::start();
			else
				session_start();

			// set the cookie by sending it in a header.
			Util::set_cookie_fix_domain(
				session_name(),
				session_id(),
				time() + $config->get( 'session.lifetime' ),
				'/',
				$req->host(),
				$req->isSecure(),
				true
			);
			
			// update the session in our request
			$req->setSession( $_SESSION );
		}

		/* CLI Requests */

		if( $req->isCli() )
		{
			global $argc, $argv;
			if( $argc >= 2 )
				$req->setPath( $argv[ 1 ] );
		}

		/* Router */

		Router::configure( [
			'namespace' => '\\app' ] );

		/* Middleware */

		foreach( (array)$config->get( 'modules.middleware' ) as $module )
		{
			$class = '\\app\\' . $module . '\\Controller';
			$controller = new $class( $app );
			$controller->middleware( $req, $res );
		}
	}

	function go()
	{
		$config = $this[ 'config' ];
		$req = $this[ 'req' ];
		$res = $this[ 'res' ];

		/*
			Routing Steps:
			1) routes from Config
			2) module routes (i.e. /users/:id/friends)
			   i) static routes
			   ii) dynamic routes
			// TODO try to remove this
			3) module admin routes
			4) view without a controller (i.e. /contact-us displays views/contact-us.tpl)
			5) not found
		*/

		$routed = false;
		$routeStep = 1;

		while( !$routed )
		{
			if( $routeStep == 1 )
			{
				/* main routes */
				$routed = Router::route( $config->get( 'routes' ), $this, $req, $res );
			}
			else if( $routeStep == 2 )
			{
				/* module routes */
			
				// check if the first part of the path is a controller
				$module = $req->paths( 0 );

				$controller = '\\app\\' . $module . '\\Controller';
				
				if( class_exists( $controller ) )
				{
					$moduleRoutes = Util::array_value( $controller::$properties, 'routes' );
					
					$req->setParams( [ 'controller' => $module . '\\Controller' ] );
					
					$routed = Router::route( $moduleRoutes, $this, $req, $res );
				}
			}
			else if( $routeStep == 3 )
			{
				/* module admin routes */
				
				if( $req->paths( 0 ) == 'admin' )
				{
					$module = $req->paths( 1 );
					
					$controller = '\\app\\' . $module . '\\Controller';

					if( class_exists( $controller ) )
					{
						$moduleInfo = $controller::$properties;

						$moduleRoutes = Util::array_value( $moduleInfo, 'routes' );

						$req->setParams( [ 'controller' => $module . '\\Controller' ] );

						$adminViewParams = [
							'selectedModule' => $module,
							'title' => Util::array_value( $moduleInfo, 'title' ) ];

						$adminLib = '\\app\\admin\\libs\\Admin';
						if( class_exists( $adminLib ) )
							$adminViewParams[ 'modulesWithAdmin' ] = $adminLib::adminModules();
						
						$this[ 'view_engine' ]->assignData( $adminViewParams );
						
						$routed = Router::route( $moduleRoutes, $this, $req, $res );
					}
				}
			}
			else if( $routeStep == 4 )
			{
				/* view without a controller */
				$basePath = $req->path();
				
				// make sure the route does not touch any special files
				if( strpos( $basePath, '/emails/' ) !== 0 && !in_array( $basePath, [ '/error', '/parent' ] ) )
				{
					$view = substr_replace( $basePath, '', 0, 1 );

					if( file_exists( INFUSE_VIEWS_DIR . '/' . $view . '.tpl' ) )
						$routed = $res->render( $view );
				}
			}
			else
			{
				/* not found */
				$res->setCode( 404 );
				
				$routed = true;
			}
			
			// move on to the next step
			$routeStep++;
		}

		// send the response
		$res->send( $req );
	}

	/**
	 * Installs the schema in the database for everything needed
	 * by the framework, including all model schema. This function
	 * does not overwrite any existing data.
	 *
	 * @param boolean $echoOutput
	 * @param boolean $cleanup when true, cleans up schema
	 *
	 * @return boolean success
	 */
	function installSchema( $echoOutput = false, $cleanup = false )
	{
		$success = true;

		if( $echoOutput )
			echo "-- Installing schema...\n";

		// database sessions
		if( $this[ 'config' ]->get( 'session.adapter' ) == 'database' )
			$success = Session\Database::install() && $success;

		// models
		foreach( $this[ 'config' ]->get( 'modules.all' ) as $module )
		{
			$controller = '\\app\\' . $module . '\\Controller';

			if( !class_exists( $controller ) || !isset( $controller::$properties ) )
				continue;

			foreach( (array)Util::array_value( $controller::$properties, 'models' ) as $model )
			{
				$modelClass = '\\app\\' . $module . '\\models\\' . $model;

				if( $echoOutput )
					echo "Updating $model...";

				$result = $modelClass::updateSchema( $cleanup );

				if( $echoOutput )
					echo ($result) ? "ok\n" : "not ok\n";

				if( !$result )
					print_r( $this[ 'errors' ]->errors() );

				$success = $result && $success;
			}
		}

		if( $echoOutput )
		{
			if( $success )
				echo "-- Schema installed successfully\n";
			else
				echo "-- Problem installing schema\n";
		}

		return $success;
	}
}