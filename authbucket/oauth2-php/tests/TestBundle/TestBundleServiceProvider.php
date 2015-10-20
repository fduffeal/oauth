<?php

/**
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBucket\OAuth2\Tests\TestBundle;

use AuthBucket\OAuth2\Tests\TestBundle\Controller\DefaultController;
use AuthBucket\OAuth2\Tests\TestBundle\Controller\DemoController;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Test bundle provider.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class TestBundleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['authbucket_oauth2.tests.default_controller'] = $app->share(function () {
            return new DefaultController();
        });

        $app['authbucket_oauth2.tests.demo_controller'] = $app->share(function () {
            return new DemoController();
        });
    }

    public function boot(Application $app)
    {
    }
}
