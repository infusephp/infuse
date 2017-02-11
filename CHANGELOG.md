# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
### Changed
- Use new PHPUnit namespacing.

## 1.4.2 - 2017-01-17
### Added
- Added an optional `sessions.name` setting for changing the name of in-app sessions.

### Changed
- PDO service no longer causes application to die on connection errors.

## 1.4.1 - 2016-10-04
### Changed
- Register session_write_close as a shutdown function when using sessions.

### Removed
- Removed deprecated `injectApp()` method in `HasApp` trait.

## 1.4 - 2016-04-30
### Added
- Added an exception handler service that catches exceptions during request handling.
- Added PHP 7 error handler service that catches PHP 7 errors during request handling.
- Added not found handler service to build a response when a request could not be routed.
- Added method not allowed handler service to build a response when a request had the correct route but wrong method.
- Added `getApp()` and `setApp()` to `HasApp` trait.
- A global default application instance is now available and returned by default in `getApp()`.
- Added executable `infuse` console command to bin dir.

### Changed
- Update infuse/libs to v0.6.
- Support Symfony 3.
- Application environment is now passed through second argument of `Application` constructor.
- Refactored request handling.
- Refactored middleware. Middleware functions now use the signature `($req, $res, $next)` and must return a `Response`. Can be added with `middleware()`.
- Route dispatching is now a middleware that is always executed last.
- Moved session starting logic into a session middleware. Must be explicitly added.
- Routes may accept a third argument that is an array containing the route parameters.

### Removed
- Removed timezone setting (should be configured through PHP instead).
- Removed error reporting and display settings (should be configured through PHP instead).
- Removed module-specific code from PHPUnit test listener (moved to infuse/auth and infuse/email projects).
- Removed JAQB database service (moved to JAQB project).
- Removed Pulsar model driver service (moved to Pulsar project).
- Removed error stack service.
- Removed `migrate` console command (moved to infuse/migrations project).

### Deprecated
- `injectApp()` in `HasApp` trait.

### Fixed
- Optimize console command uses original configuration on subsequent calls.

## 1.3 - 2016-01-03
### Added
- Support for custom console commands added.
- Added `optimize` console command to cache routing table and configuration.
- Custom services can now be specified and the built-in services overridden.

### Changed
- Update infuse/libs to v0.5.
- Move all classes to `Infuse` namespace.
- Rename `App` to `Application`.
- A few settings have been moved/renamed.

### Fixed
- Various bug fixes

## 1.2 - 2015-04-08
### Added
- Support for new session handlers.

### Changed
- Update infuse/libs to v0.4
- Remove references to deprecated `infuse\Database` class.
- Switch to JAQB for database interactions.
- Move caching to Stash.
- Rename CLI tool to `infuse`.
- Rename `TestBootstrap` class to `Test`.

### Fixed
- Various bug fixes

## 1.1 - 2014-11-25
### Added
- Added new QueryBuilder from infuse/libs.
- Added PDO connection.

### Changed
- Use v0.3.0 of infuse/libs.
- Tests pass against HHVM.

### Fixed
- Various bug fixes.

## 1.0 - 2014-09-25
### Added
- Initial release!