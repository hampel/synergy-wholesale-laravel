<?php

declare(strict_types=1);

namespace Hampel\SynergyWholesale\Laravel;

use Hampel\SynergyWholesale\Laravel\Exception\MissingCredentials;
use Hampel\SynergyWholesale\SynergyWholesale;
use Hampel\SynergyWholesale\Transport\SoapTransport;
use Hampel\SynergyWholesale\Transport\Transport;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Wires the Synergy Wholesale client into the container.
 *
 * Everything this package knows about the API is in that one binding: the client itself
 * is generated from the WSDL and lives in hampel/synergy-wholesale.
 */
final class SynergyWholesaleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/synergy-wholesale.php', 'synergy-wholesale');

        // Bound separately, and by interface, because Transport is the client's only
        // extension point: SynergyWholesale is final and its API classes are generated,
        // so caching, retries, rate limiting and a fixture in an application's own tests
        // all attach here. Rebind or decorate this and the client below picks it up.
        $this->app->singleton(Transport::class, static fn (): Transport => SoapTransport::make());

        $this->app->singleton(SynergyWholesale::class, function (): SynergyWholesale {
            $config = $this->app->make(Config::class);

            // with() rather than make(): make() would build a SoapTransport of its own and
            // seal it in, defeating the binding above.
            return SynergyWholesale::with(
                $this->app->make(Transport::class),
                $this->credential($config, 'reseller_id', 'SYNERGY_WHOLESALE_RESELLER_ID'),
                $this->credential($config, 'api_key', 'SYNERGY_WHOLESALE_API_KEY'),
                $this->app->make(LoggerInterface::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // configPath() rather than the config_path() helper: the helper is defined by
            // illuminate/foundation, which this package does not require and should not.
            // Requiring only illuminate/support and illuminate/contracts is a claim that
            // the package needs no full application, and Testbench -- which boots one --
            // would never catch the helper contradicting it.
            $this->publishes([
                __DIR__.'/../config/synergy-wholesale.php' => $this->app->configPath('synergy-wholesale.php'),
            ], 'synergy-wholesale-config');
        }
    }

    /**
     * Reads one credential, refusing to build a client without it.
     *
     * An unset credential otherwise reaches the API as an empty string and comes back as
     * ERR_RESELLER_NOT_AUTHORISED -- which is also what a correct key from an IP that is
     * not on the allowlist returns, so the two failures are indistinguishable at the point
     * where you can least afford to confuse them. Failing here separates them.
     */
    private function credential(Config $config, string $key, string $env): string
    {
        $value = $config->get("synergy-wholesale.{$key}");

        if (! is_string($value) || $value === '') {
            throw new MissingCredentials(
                "Synergy Wholesale is not configured: set {$env} in the environment, "
                ."or synergy-wholesale.{$key} in the published config."
            );
        }

        return $value;
    }
}
