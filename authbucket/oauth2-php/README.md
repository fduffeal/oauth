AuthBucket\\OAuth2
==================

[![Build
Status](https://travis-ci.org/authbucket/oauth2-php.svg?branch=master)](https://travis-ci.org/authbucket/oauth2-php)
[![Coverage
Status](https://coveralls.io/repos/authbucket/oauth2-php/badge.svg?branch=master&service=github)](https://coveralls.io/github/authbucket/oauth2-php?branch=master)
[![Dependency
Status](https://www.versioneye.com/php/authbucket:oauth2-php/dev-master/badge.svg)](https://www.versioneye.com/php/authbucket:oauth2-php/dev-master)
[![Latest Stable
Version](https://poser.pugx.org/authbucket/oauth2-php/v/stable.svg)](https://packagist.org/packages/authbucket/oauth2-php)
[![Total
Downloads](https://poser.pugx.org/authbucket/oauth2-php/downloads.svg)](https://packagist.org/packages/authbucket/oauth2-php)
[![License](https://poser.pugx.org/authbucket/oauth2-php/license.svg)](https://packagist.org/packages/authbucket/oauth2-php)

The primary goal of
[AuthBucket\\OAuth2](http://oauth2-php.authbucket.com/) is to develop a
standards compliant [RFC6749
OAuth2.0](http://tools.ietf.org/html/rfc6749) library; secondary goal
would be develop corresponding wrapper [Symfony2
Bundle](http://symfony.com) and [Drupal module](https://www.drupal.org).

This library bundle with a [Silex](http://silex.sensiolabs.org/) based
[AuthBucketOAuth2ServiceProvider](https://github.com/authbucket/oauth2-php/blob/master/src/Provider/AuthBucketOAuth2ServiceProvider.php)
for unit test and demo purpose. Installation and usage can refer as
below.

Installation
------------

Simply add a dependency on `authbucket/oauth2-php` to your project's
`composer.json` file if you use [Composer](http://getcomposer.org/) to
manage the dependencies of your project.

Here is a minimal example of a `composer.json`:

    {
        "require": {
            "authbucket/oauth2-php": "~3.0"
        }
    }

### Parameters

The bundled
[AuthBucketOAuth2ServiceProvider](https://github.com/authbucket/oauth2-php/blob/master/src/Provider/AuthBucketOAuth2ServiceProvider.php)
come with following parameters:

-   `authbucket_oauth2.model`: (Optional) Override this with your own
    model classes, default with in-memory AccessToken for using resource
    firewall with remote debug endpoint.
-   `authbucket_oauth2.model_manager.factory`: (Optional) Override this
    with your backend model managers, e.g. Doctrine ORM
    EntityRepository, default with in-memory implementation for using
    resource firewall with remote debug endpoint.
-   `authbucket_oauth2.user_provider`: (Optional) For using
    `grant_type = password`, override this parameter with your own user
    provider, e.g. using InMemoryUserProvider or a Doctrine ORM
    EntityRepository that implements UserProviderInterface.

### Services

The bundled
[AuthBucketOAuth2ServiceProvider](https://github.com/authbucket/oauth2-php/blob/master/src/Provider/AuthBucketOAuth2ServiceProvider.php)
come with following services controller which simplify the OAuth2.0
controller implementation overhead:

-   `authbucket_oauth2.oauth2_controller`: OAuth2 endpoint controller.

### Registering

If you are using [Silex](http://silex.sensiolabs.org/), register
[AuthBucketOAuth2ServiceProvider](https://github.com/authbucket/oauth2-php/blob/master/src/Provider/AuthBucketOAuth2ServiceProvider.php)
as below:

    $app->register(new AuthBucket\OAuth2\Provider\AuthBucketOAuth2ServiceProvider());

Moreover, enable following service providers if that's not already the
case:

    $app->register(new Silex\Provider\MonologServiceProvider());
    $app->register(new Silex\Provider\SecurityServiceProvider());
    $app->register(new Silex\Provider\ValidatorServiceProvider());

Usage
-----

This library seperate the endpoint logic in frontend firewall and
backend controller point of view, so you will need to setup both for
functioning.

To enable the built-in controller with corresponding routing, you need
to mount it manually:

    $app->get('/api/oauth2/authorize', 'authbucket_oauth2.oauth2_controller:authorizeAction')
        ->bind('api_oauth2_authorize');

    $app->post('/api/oauth2/token', 'authbucket_oauth2.oauth2_controller:tokenAction')
        ->bind('api_oauth2_token');

    $app->match('/api/oauth2/debug', 'authbucket_oauth2.oauth2_controller:debugAction')
        ->bind('api_oauth2_debug');

Below is a list of recipes that cover some common use cases.

### Authorization Endpoint

We don't provide custom firewall for this endpoint, which you should
protect it by yourself, authenticate and capture the user credential,
e.g. by
[SecurityServiceProvider](http://silex.sensiolabs.org/doc/providers/security.html):

    $app['security.encoder.digest'] = $app->share(function ($app) {
        return new Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder();
    });

    $app['security.user_provider.default'] = $app['security.user_provider.inmemory._proto']([
        'demousername1' => ['ROLE_USER', 'demopassword1'],
        'demousername2' => ['ROLE_USER', 'demopassword2'],
        'demousername3' => ['ROLE_USER', 'demopassword3'],
    ]);

    $app['security.firewalls'] = [
        'api_oauth2_authorize' => [
            'pattern' => '^/api/oauth2/authorize$',
            'http' => true,
            'users' => $app['security.user_provider.default'],
        ],
    ];

### Token Endpoint

Similar as authorization endpoint, we need to protect this endpoint with
our custom firewall `oauth2_token`:

    $app['security.firewalls'] = [
        'api_oauth2_token' => [
            'pattern' => '^/api/oauth2/token$',
            'oauth2_token' => true,
        ],
    ];

### Debug Endpoint

We should protect this endpoint with our custom firewall
`oauth2_resource`:

    $app['security.firewalls'] = [
        'api_oauth2_debug' => [
            'pattern' => '^/api/oauth2/debug$',
            'oauth2_resource' => true,
        ],
    ];

### Resource Endpoint

We don't provide other else resource endpoint controller implementation
besides above debug endpoint. You should consider implement your own
endpoint with custom logic, e.g. fetching user email address or profile
image.

On the other hand, you can protect your resource server endpoint with
our custom firewall `oauth2_resource`. Shorthand version (default assume
resource server bundled with authorization server, query local model
manager, without scope protection):

    $app['security.firewalls'] = [
        'api_resource' => [
            'pattern' => '^/api/resource',
            'oauth2_resource' => true,
        ],
    ];

Longhand version (assume resource server bundled with authorization
server, query local model manager, protect with scope `demoscope1`):

    $app['security.firewalls'] = [
        'api_resource' => [
            'pattern' => '^/api/resource',
            'oauth2_resource' => [
                'resource_type' => 'model',
                'scope' => ['demoscope1'],
            ],
        ],
    ];

If authorization server is hosting somewhere else, you can protect your
local resource endpoint by query remote authorization server debug
endpoint:

    $app['security.firewalls'] = [
        'api_resource' => [
            'pattern' => '^/api/resource',
            'oauth2_resource' => [
                'resource_type' => 'debug_endpoint',
                'scope' => ['demoscope1'],
                'options' => [
                    'debug_endpoint' => 'http://example.com/api/oauth2/debug',
                    'cache' => true,
                ],
            ],
        ],
    ];

Demo
----

The demo is based on [Silex](http://silex.sensiolabs.org/) and
[AuthBucketOAuth2ServiceProvider](https://github.com/authbucket/oauth2-php/blob/master/src/Provider/AuthBucketOAuth2ServiceProvider.php).
Read though [Demo](http://oauth2-php.authbucket.com/demo) for more
information.

You may also run the demo locally. Open a console and execute the
following command to install the latest version in the `oauth2-php`
directory:

    $ composer create-project authbucket/oauth2-php authbucket/oauth2-php "~3.0"

Then use the PHP built-in web server to run the demo application:

    $ cd authbucket/oauth2-php
    $ ./app/console server:run

If you get the error
`There are no commands defined in the "server" namespace.`, then you are
probably using PHP 5.3. That's ok! But the built-in web server is only
available for PHP 5.4.0 or higher. If you have an older version of PHP
or if you prefer a traditional web server such as Apache or Nginx, read
the [Configuring a web
server](http://silex.sensiolabs.org/doc/web_servers.html) article.

Open your browser and access the <http://127.0.0.1:8000> URL to see the
Welcome page of demo application.

Also access <http://127.0.0.1:8000/admin/refresh_database> to initialize
the bundled SQLite database with user account `admin`:`secrete`.

Documentation
-------------

OAuth2's documentation is built with
[Sami](https://github.com/fabpot/Sami) and publicly hosted on [GitHub
Pages](http://authbucket.github.io/oauth2-php).

To built the documents locally, execute the following command:

    $ composer sami

Open `build/sami/index.html` with your browser for the documents.

Tests
-----

This project is coverage with [PHPUnit](http://phpunit.de/) test cases;
CI result can be found from [Travis
CI](https://travis-ci.org/authbucket/oauth2-php); code coverage report
can be found from
[Coveralls](https://coveralls.io/r/authbucket/oauth2-php).

To run the test suite locally, execute the following command:

    $ composer phpunit

Open `build/logs/html` with your browser for the coverage report.

References
----------

-   [RFC6749](http://tools.ietf.org/html/rfc6749)
-   [Demo](http://oauth2-php.authbucket.com/demo)
-   [API](http://authbucket.github.io/oauth2-php/)
-   [GitHub](https://github.com/authbucket/oauth2-php)
-   [Packagist](https://packagist.org/packages/authbucket/oauth2-php)
-   [Travis CI](https://travis-ci.org/authbucket/oauth2-php)
-   [Coveralls](https://coveralls.io/r/authbucket/oauth2-php)

License
-------

-   Code released under
    [MIT](https://github.com/authbucket/oauth2-php/blob/master/LICENSE)
-   Docs released under [CC BY
    4.0](http://creativecommons.org/licenses/by/4.0/)
