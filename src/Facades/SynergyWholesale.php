<?php

declare(strict_types=1);

namespace Hampel\SynergyWholesale\Laravel\Facades;

use Hampel\SynergyWholesale\Generated\Api\CategoriesApi;
use Hampel\SynergyWholesale\Generated\Api\DnsApi;
use Hampel\SynergyWholesale\Generated\Api\DnssecApi;
use Hampel\SynergyWholesale\Generated\Api\DomainsApi;
use Hampel\SynergyWholesale\Generated\Api\ForwardingApi;
use Hampel\SynergyWholesale\Generated\Api\HostingApi;
use Hampel\SynergyWholesale\Generated\Api\RegistryHostsApi;
use Hampel\SynergyWholesale\Generated\Api\SmsApi;
use Hampel\SynergyWholesale\Generated\Api\SslApi;
use Hampel\SynergyWholesale\Generated\Api\SubscriptionsApi;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Synergy Wholesale client.
 *
 * The operations are one hop further in -- SynergyWholesale::domains()->checkDomain(...) --
 * so the annotations below cover the hop and the generated API classes carry the typed
 * signatures from there. Without them every call through the facade is untyped to both the
 * IDE and PHPStan, which is most of what a facade costs you.
 *
 * @method static CategoriesApi categories()
 * @method static DnsApi dns()
 * @method static DnssecApi dnssec()
 * @method static DomainsApi domains()
 * @method static ForwardingApi forwarding()
 * @method static HostingApi hosting()
 * @method static RegistryHostsApi registryHosts()
 * @method static SmsApi sms()
 * @method static SslApi ssl()
 * @method static SubscriptionsApi subscriptions()
 *
 * @see \Hampel\SynergyWholesale\SynergyWholesale
 */
final class SynergyWholesale extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hampel\SynergyWholesale\SynergyWholesale::class;
    }
}
