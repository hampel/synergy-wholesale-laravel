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
use ReflectionProperty;

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
        //
        // That covers register() only. boot() is the same problem and needs its own test -
        // see booting_the_provider_registers_the_config_to_publish below.
        $app = new Application(__DIR__.'/..');
        $app->instance('config', new ConfigRepository());

        (new SynergyWholesaleServiceProvider($app))->register();

        $this->assertTrue($app->bound(Transport::class));
        $this->assertTrue($app->bound(SynergyWholesale::class));
        $this->assertSame('', $app->make(Config::class)->get('synergy-wholesale.api_key'));
    }

    #[Test]
    public function booting_the_provider_registers_the_config_to_publish(): void
    {
        // The other half of the test above, and not reached by it. Testbench boots the
        // application inside parent::setUp(), before withoutDeprecationHandling() puts
        // PHPUnit's error handler back - so boot() has always already run under Laravel's
        // swallowing handler, and a deprecation raised by configPath() or publishes() on some
        // future framework version would ship in silence. Probed: without this test, a
        // deprecation in boot() exits 0 and prints OK; with it, exit 2.
        //
        // ServiceProvider::$publishes is static and Testbench has already filled it, so
        // booting a second application overwrites the destination path that
        // the_config_file_is_publishable_under_its_own_tag asserts on - and which of the two
        // fails would depend on execution order. Hence the snapshot and the finally.
        $publishes = new ReflectionProperty(ServiceProvider::class, 'publishes');
        $groups = new ReflectionProperty(ServiceProvider::class, 'publishGroups');
        $savedPublishes = $publishes->getValue();
        $savedGroups = $groups->getValue();

        try {
            // Emptied, not just restored afterwards. Testbench's own boot registered an entry
            // naming the SAME source path this one would, so against the filled statics the
            // assertion below cannot tell whose registration it sees, and passes even when the
            // provider under test registered nothing. Deleting publishes() from boot() does not
            // reveal that, because Testbench's boot runs the same code; making only this
            // application report runningInConsole() as false does.
            $publishes->setValue(null, []);
            $groups->setValue(null, []);

            $app = new Application(__DIR__.'/..');
            $app->instance('config', new ConfigRepository());

            $provider = new SynergyWholesaleServiceProvider($app);
            $provider->register();
            $provider->boot();

            // By source path rather than destination: the destination is this throwaway
            // application's config directory, which says nothing about the package.
            $this->assertSame(
                [realpath(__DIR__.'/../config/synergy-wholesale.php')],
                array_map(
                    'realpath',
                    array_keys(ServiceProvider::pathsToPublish(
                        SynergyWholesaleServiceProvider::class,
                        'synergy-wholesale-config',
                    )),
                ),
            );
        } finally {
            $publishes->setValue(null, $savedPublishes);
            $groups->setValue(null, $savedGroups);
        }
    }
}
