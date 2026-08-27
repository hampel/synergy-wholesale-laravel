<?php

declare(strict_types=1);

namespace Hampel\SynergyWholesale\Laravel\Tests;

use Hampel\SynergyWholesale\Laravel\Facades\SynergyWholesale;
use Hampel\SynergyWholesale\Laravel\SynergyWholesaleServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Testbench boots a minimal Laravel application from inside this package, so the Laravel
 * version under test comes from Composer resolution rather than from an installed framework.
 * Never install a framework to test a package.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [SynergyWholesaleServiceProvider::class];
    }

    /**
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return ['SynergyWholesale' => SynergyWholesale::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('synergy-wholesale.reseller_id', 'reseller-under-test');
        $app['config']->set('synergy-wholesale.api_key', 'key-under-test');
    }

    /**
     * The application, narrowed.
     *
     * Testbench declares $app as nullable because it does not exist before setUp, so
     * every use of it in a test is otherwise a call on Application|null.
     */
    protected function container(): Application
    {
        $app = $this->app;

        $this->assertNotNull($app);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Laravel's HandleExceptions bootstrapper replaces PHPUnit's error handler when the
        // app boots, and shouldIgnoreDeprecationErrors() discards deprecations outright
        // while running tests - so phpunit.xml's failOnDeprecation never sees one and is
        // inert in any Testbench-based package. This throws on them instead, which is the
        // whole point of the flag: a library should hear about a deprecation before its
        // users do.
        $this->withoutDeprecationHandling();
    }
}
