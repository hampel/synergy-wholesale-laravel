<?php

declare(strict_types=1);

namespace Hampel\SynergyWholesale\Laravel\Tests;

use Hampel\SynergyWholesale\Laravel\SynergyWholesaleServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;
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
}
