<?php

declare(strict_types=1);

namespace Hampel\SynergyWholesale\Laravel\Tests;

use Hampel\SynergyWholesale\Laravel\SynergyWholesaleServiceProvider;
use Hampel\SynergyWholesale\SynergyWholesale;
use Hampel\SynergyWholesale\Transport\Transport;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\Test;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function the_package_config_is_merged_into_the_application(): void
    {
        $config = $this->container()->make(Config::class);

        $this->assertTrue($config->has('synergy-wholesale.reseller_id'));
        $this->assertTrue($config->has('synergy-wholesale.api_key'));
    }

    #[Test]
    public function the_config_file_is_publishable_under_its_own_tag(): void
    {
        $published = ServiceProvider::pathsToPublish(
            SynergyWholesaleServiceProvider::class,
            'synergy-wholesale-config',
        );

        $this->assertSame(
            [config_path('synergy-wholesale.php')],
            array_values($published),
        );
    }

    #[Test]
    public function the_shipped_config_defaults_to_empty_credentials(): void
    {
        // Read straight from the file rather than the merged config, which the test case
        // has already overridden. An unset environment must produce the empty string the
        // provider rejects - not null, and not a stale value from somewhere else.
        $defaults = require __DIR__.'/../config/synergy-wholesale.php';

        $this->assertSame(['api_key' => '', 'reseller_id' => ''], $defaults);
    }

    #[Test]
    public function registering_the_provider_binds_the_client_and_merges_the_config(): void
    {
        // Registered here, against an application built in the test body, rather than
        // relying on the registration Testbench already did in setUp. Two reasons, and the
        // second is the important one:
        //
        // The bindings are asserted against an application that did not have them, so the
        // assertions depend on this call rather than on setUp's.
        //
        // And register() only runs under PHPUnit's error handler if it runs from here.
        // Laravel's HandleExceptions bootstrapper replaces that handler while the
        // application boots, which in a Testbench suite is during parent::setUp() - before
        // withoutDeprecationHandling() puts it back. So a deprecation raised by the
        // provider's own registration during setUp is discarded, and phpunit.xml's
        // failOnDeprecation never sees it. Verified by probe: E_USER_DEPRECATED in
        // register() passes the suite when it fires during setUp, and fails it from here.
        $app = new Application(__DIR__.'/..');
        $app->instance('config', new ConfigRepository());

        (new SynergyWholesaleServiceProvider($app))->register();

        $this->assertTrue($app->bound(Transport::class));
        $this->assertTrue($app->bound(SynergyWholesale::class));
        $this->assertSame('', $app->make(Config::class)->get('synergy-wholesale.api_key'));
    }
}
