# Synergy Wholesale API for Laravel

[![Tests](https://github.com/hampel/synergy-wholesale-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/hampel/synergy-wholesale-laravel/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://packagist.org/packages/hampel/synergy-wholesale-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://packagist.org/packages/hampel/synergy-wholesale-laravel)
[![Open Issues](https://img.shields.io/github/issues-raw/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://github.com/hampel/synergy-wholesale-laravel/issues)
[![License](https://img.shields.io/packagist/l/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://packagist.org/packages/hampel/synergy-wholesale-laravel)

Laravel service provider and facade for
[hampel/synergy-wholesale](https://github.com/hampel/synergy-wholesale), a client for the Synergy
Wholesale reseller API covering 138 operations across domains, DNS, DNSSEC, SSL, hosting, email and
URL forwarding, SMS and subscriptions.

By [Simon Hampel](mailto:simon@hampelgroup.com)

Everything you can call, and every type it returns, is documented in that package. This one adds
the container binding, a facade and a config file.

## Installation

```bash
composer require hampel/synergy-wholesale-laravel
```

Generate an API key in the Synergy Wholesale control panel and put it, with your reseller ID, in
`.env`:

```ini
SYNERGY_WHOLESALE_API_KEY=your_synergy_wholesale_api_key
SYNERGY_WHOLESALE_RESELLER_ID=your_synergy_wholesale_reseller_id
```

The API authorises by IP address as well as by key, so every machine that calls it — including
each web server, queue worker and developer machine — needs its address on the allowlist in the
control panel. A correct key from an unlisted address fails with `ERR_RESELLER_NOT_AUTHORISED`,
which reads like a bad key.

Publishing the config file is optional and only useful if you want to read the credentials from
somewhere other than those two environment variables:

```bash
php artisan vendor:publish --tag=synergy-wholesale-config
```

### Laravel Zero

**Laravel Zero does not run package discovery**, so installing the package registers nothing: the
client is not bound and `config('synergy-wholesale')` is null until the provider is listed by hand
in `config/app.php`:

```php
'providers' => [
    AppServiceProvider::class,
    Hampel\SynergyWholesale\Laravel\SynergyWholesaleServiceProvider::class,
],
```

The global `SynergyWholesale` alias comes from discovery too, so it stays unavailable. The facade
works when imported by its class name, as the examples below do.

**Inject the client into `handle()`, not into a command's constructor.** Resolving the client throws
`MissingCredentials` when no reseller ID or API key is configured, and Laravel Zero constructs every
command to build its command list. A constructor dependency on the client therefore breaks `list`,
and `--help` for every other command in the application, on any machine without credentials.
Injected into `handle()`, the exception is raised only when that command runs:

```php
use Hampel\SynergyWholesale\SynergyWholesale;

public function handle(SynergyWholesale $sw): int
{
    // ...
}
```

## Usage

Operations are grouped, and the method name is the API operation name as published:

```php
use Hampel\SynergyWholesale\Laravel\Facades\SynergyWholesale;

$result = SynergyWholesale::domains()->checkDomain(domainName: 'example.com');
$status = SynergyWholesale::ssl()->getCertStatus(certID: 'abc123');
```

Or inject the client, which is bound as a singleton:

```php
use Hampel\SynergyWholesale\SynergyWholesale;

public function __construct(private readonly SynergyWholesale $sw) {}

public function available(string $domain): bool
{
    return $this->sw->domains()->checkDomain(domainName: $domain)->available === 1;
}
```

A failed call throws: `ApiError` when the API answers with an `ERR_` status, `TransportException`
when the call could not be completed, and `MissingCredentials` when the reseller ID or API key is
not configured. All three implement `Hampel\SynergyWholesale\Exception\SynergyWholesaleException`,
so one `catch` covers the package.

Every call is logged through the application's default logger — one `info` line per operation, and
the request and response at `debug` with the API key, EPP auth codes and passwords redacted.

### Caching, retries and other decoration

This package binds `Hampel\SynergyWholesale\Transport\Transport` separately from the client, and
that binding is the extension point. Anything that wraps a call — caching, retries, rate limiting,
or a fixture in your own tests — is a decorator implementing that interface:

```php
$this->app->extend(Transport::class, fn (Transport $inner) => new CachingTransport($inner, cache()));
```

Nothing is cached by default. Prices, availability and domain details are all cacheable in
principle, but for how long is an application's decision rather than this package's, and a wrong
answer about availability is expensive.

## Upgrading from 1.x

Version 2 is a rewrite with no API in common with 1.x, and neither is `hampel/synergy-wholesale`
itself — the `Command` and `Response` classes are gone, replaced by generated typed requests and
responses reached through operation groups. Read the upgrade table in the
[core package README](https://github.com/hampel/synergy-wholesale#upgrading-from-1x) first; then,
in your application:

| 1.x | 2.x |
|---|---|
| `SynergyWholesale\Facades\SynergyWholesale` | `Hampel\SynergyWholesale\Laravel\Facades\SynergyWholesale` |
| `App::make('SynergyWholesale\SynergyWholesale')` | `App::make(Hampel\SynergyWholesale\SynergyWholesale::class)` |
| `$sw->execute(new CheckDomainCommand(new Domain('example.com')))` | `$sw->domains()->checkDomain(domainName: 'example.com')` |
| `$sw->checkDomain($command, fresh: true)` | no equivalent — nothing is cached, so nothing is stale |
| `config('synergy-wholesale.cache.*')` | removed |
| `--tag=config` | `--tag=synergy-wholesale-config` |

## Requirements

PHP 8.3 or later with `ext-soap`, and Laravel 12 or 13.
