<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @copyright 2015 Jared King
 * @license MIT
 */
namespace Infuse;

class RouteResolver
{
    use HasApp;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $defaultController;

    /**
     * @var string
     */
    private $defaultAction = 'index';

    /**
     * Set the namespace.
     *
     * @param string $namespace
     *
     * @return self
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Gets the namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the default controller class.
     *
     * @param string $class
     *
     * @return self
     */
    public function setDefaultController($class)
    {
        $this->defaultController = $class;

        return $this;
    }

    /**
     * Gets the default controller class.
     *
     * @return string
     */
    public function getDefaultController()
    {
        return $this->defaultController;
    }

    /**
     * Set the default action.
     *
     * @param string $defaultAction
     *
     * @return self
     */
    public function setDefaultAction($defaultAction)
    {
        $this->defaultAction = $defaultAction;

        return $this;
    }

    /**
     * Gets the default action.
     *
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * Executes a route handler.
     *
     * @param array|string $route array('controller','method') or array('controller')
     *                            or 'method'
     * @param Request      $req
     * @param Response     $res
     * @param array        $args
     *
     * @throws Exception when the route cannot be resolved.
     *
     * @return Response
     */
    public function resolve($route, $req, $res, array $args)
    {
        $result = false;
        if (is_array($route) || is_string($route)) {
            // method name and controller supplied
            if (is_string($route) && $req->params('controller')) {
                $route = [$req->params('controller'), $route];
            // method name supplied
            } elseif (is_string($route)) {
                $route = [$this->defaultController, $route];
            // no method name? fallback to the index() method
            } elseif (count($route) == 1) {
                $route[] = $this->defaultAction;
            }

            list($controller, $method) = $route;

            $controller = $this->namespace.'\\'.$controller;

            if (!class_exists($controller)) {
                throw new \Exception("Controller does not exist: $controller");
            }

            $controllerObj = new $controller();

            // give the controller access to the DI container
            if (method_exists($controllerObj, 'setApp')) {
                $controllerObj->setApp($this->app);
            }

            // collect any preset route parameters
            if (isset($route[2])) {
                $params = $route[2];
                $req->setParams($params);
            }

            $result = $controllerObj->$method($req, $res, $args);
        } elseif (is_callable($route)) {
            $result = call_user_func($route, $req, $res, $args);
        }

        if ($result instanceof View) {
            $res->render($result);
        }

        return $res;
    }
}
