<?php

declare(strict_types=1);

namespace Hampel\SynergyWholesale\Laravel\Tests;

use Hampel\SynergyWholesale\Laravel\Exception\MissingCredentials;
use Hampel\SynergyWholesale\Laravel\Facades\SynergyWholesale as Facade;
use Hampel\SynergyWholesale\SynergyWholesale;
use Hampel\SynergyWholesale\Transport\FixtureTransport;
use Hampel\SynergyWholesale\Transport\SoapTransport;
use Hampel\SynergyWholesale\Transport\Transport;
use Illuminate\Contracts\Config\Repository as Config;
use PHPUnit\Framework\Attributes\Test;

/**
 * What this package actually contributes is one container binding, so this is where the
 * coverage belongs: that the binding exists, that configuration reaches the wire through
 * it, and that the transport underneath it can still be replaced.
 */
final class ClientResolutionTest extends TestCase
{
    #[Test]
    public function it_resolves_the_client_from_the_container(): void
    {
        $this->assertInstanceOf(SynergyWholesale::class, $this->container()->make(SynergyWholesale::class));
    }

    #[Test]
    public function the_client_is_a_singleton(): void
    {
        $this->assertSame(
            $this->container()->make(SynergyWholesale::class),
            $this->container()->make(SynergyWholesale::class),
        );
    }

    #[Test]
    public function the_facade_resolves_the_same_client(): void
    {
        $this->assertSame($this->container()->make(SynergyWholesale::class), Facade::getFacadeRoot());
    }

    #[Test]
    public function it_binds_the_soap_transport_by_default(): void
    {
        $this->assertInstanceOf(SoapTransport::class, $this->container()->make(Transport::class));
    }

    #[Test]
    public function configured_credentials_reach_the_wire(): void
    {
        $transport = $this->fakeTransport();

        $response = $this->container()->make(SynergyWholesale::class)->domains()->checkDomain('example.com');

        $this->assertSame(1, $response->available);
        $this->assertSame([[
            'operation' => 'checkDomain',
            'request' => [
                'resellerID' => 'reseller-under-test',
                'apiKey' => 'key-under-test',
                'domainName' => 'example.com',
            ],
        ]], $transport->calls);
    }

    #[Test]
    public function the_transport_binding_can_be_decorated(): void
    {
        // The whole reason the provider binds Transport separately and builds the client
        // with with() rather than make(). Caching, retries and rate limiting all attach
        // here, and a decorator is only reachable if the client resolves the binding
        // instead of constructing a transport of its own.
        $inner = $this->fakeTransport();

        $this->container()->extend(Transport::class, static fn (): Transport => new FixtureTransport([
            'checkDomain' => FixtureTransport::response(['status' => 'AVAILABLE', 'available' => 0]),
        ]));

        $response = $this->container()->make(SynergyWholesale::class)->domains()->checkDomain('example.com');

        $this->assertSame(0, $response->available);
        $this->assertSame([], $inner->calls);
    }

    #[Test]
    public function it_refuses_to_build_a_client_without_a_reseller_id(): void
    {
        $this->container()->make(Config::class)->set('synergy-wholesale.reseller_id', '');

        $this->expectException(MissingCredentials::class);
        $this->expectExceptionMessage('SYNERGY_WHOLESALE_RESELLER_ID');

        $this->container()->make(SynergyWholesale::class);
    }

    #[Test]
    public function it_refuses_to_build_a_client_without_an_api_key(): void
    {
        $this->container()->make(Config::class)->set('synergy-wholesale.api_key', '');

        $this->expectException(MissingCredentials::class);
        $this->expectExceptionMessage('SYNERGY_WHOLESALE_API_KEY');

        $this->container()->make(SynergyWholesale::class);
    }

    private function fakeTransport(): FixtureTransport
    {
        $transport = new FixtureTransport([
            'checkDomain' => FixtureTransport::response(['status' => 'AVAILABLE', 'available' => 1]),
        ]);

        $this->container()->instance(Transport::class, $transport);

        return $transport;
    }
}
