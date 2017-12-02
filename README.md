Infuse Framework
================

[![Latest Stable Version](https://poser.pugx.org/infuse/infuse/v/stable.svg?style=flat)](https://packagist.org/packages/infuse/infuse)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)
[![Build Status](https://travis-ci.org/infusephp/infuse.svg?branch=master&style=flat)](https://travis-ci.org/infusephp/infuse)
[![Coverage Status](https://coveralls.io/repos/infusephp/infuse/badge.svg?style=flat)](https://coveralls.io/r/infusephp/infuse)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/infusephp/infuse/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/infusephp/infuse/?branch=master)
[![Total Downloads](https://poser.pugx.org/infuse/infuse/downloads.svg?style=flat)](https://packagist.org/packages/infuse/infuse)

Modular framework for building modern applications in PHP

## Introduction

Infuse is a framework to help you build awesome web applications at scale (in terms of LoC and traffic). Minimalism is the primary design goal of this project that is acheived through strong use of modularity. The core framework is a simple set of patterns for gluing together PHP applications.

A minimal amount of components are included with Infuse. Just enough to bootstrap your application, provide routing, requests/responses, dependency injection, and an extensible console application. Any other components completely depends on the needs of your application and can be added through modules available on Packagist. Need an ORM? Then add Pulsar or Doctrine. What about processing scheduled tasks? There's a module for that. If a module that you need is not available then it's easy to write your own.

You will find a minimum amount of opinion in this framework. It's targeted at seasoned PHPers who have their own opinions on what components they want to build their application with. Infuse tries to stay as lightweight as possible while being extendable.

## Features

- Modular design
- Adheres to [PHP-FIG PSRs](http://www.php-fig.org/psr/) when possible
- Dependency Injection with [Pimple](https://github.com/silexphp/Pimple)
- Flexible routing via [nikic/fast-route](https://github.com/nikic/FastRoute)
- Logging via [monolog](https://github.com/Seldaek/monolog)
- Console application based on [Symfony/Console](https://github.com/symfony/console)

## Requirements

- PHP 7+

## Installation

Install the package with [composer](http://getcomposer.org):

	composer require infuse/infuse

## Available Modules

### Databases

- [Pulsar ORM](https://github.com/jaredtking/pulsar): Standalone active record implementation
- [JAQB](https://github.com/jaredtking/jaqb): Fluent database query builder that runs on top of PDO
- [infuse/migrations](https://github.com/infusephp/migrations): Database migrations powered by Phinx
- [infuse/rest-api](https://github.com/infusephp/rest-api): Quickly scaffold a RESTful API for Pulsar models
- [infuse/stash](https://github.com/infusephp/stash): Add caching to Pulsar models using Stash

### Authentication

- [infuse/auth](https://github.com/infusephp/auth): User authentication and management
- [infuse/oauth2](https://github.com/infusephp/oauth2): Adds support for OAuth2 and JWT access tokens
- [infuse/facebook](https://github.com/infusephp/facebook): Adds Facebook as an authentication method
- [infuse/twitter](https://github.com/infusephp/twitter): Adds Twitter as an authentication method
- [infuse/instagram](https://github.com/infusephp/instagram): Adds Instagram as an authentication method

### Payments

- [infuse/billing](https://github.com/infusephp/billing): Implementation of a subscription membership system powered by Stripe

### Services

- [infuse/cron](https://github.com/infusephp/cron): Process scheduled tasks in the background for your app
- [infuse/email](https://github.com/infusephp/email): Provides a mailer to queue and sending email templates using Swiftmailer
- [infuse/iron-mq](https://github.com/infusephp/iron-mq): Adds Iron.io push queues to the Infuse queue system

### Administration

- [infuse/admin](https://github.com/infusephp/admin): Generates an admin panel for managing your application and Pulsar models
- [infuse/statistics](https://github.com/infusephp/statistics): Statistics addon to the admin dashboard

## Contributing

Please feel free to contribute by participating in the issues or by submitting a pull request. :-)

### Tests

Use phpunit to run the included tests:

	phpunit

## License

The MIT License (MIT)

Copyright © 2015 Jared King

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.