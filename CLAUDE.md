# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`hampel/synergy-wholesale-laravel` — Laravel wiring for
[`hampel/synergy-wholesale`](https://github.com/hampel/synergy-wholesale), the generated client for
the Synergy Wholesale reseller SOAP API. A service provider, a facade, a config file and one
exception.

No API knowledge lives here and none should. Operations, request and response types, the envelope
rule, credential redaction and the transport all belong to the core package; if a change needs to
know what an operation does, it is being made in the wrong repository.

Version 2 is a rewrite for core v2 and shares nothing with 1.x. The 1.x package subclassed the
client to add caching, which v2 makes impossible and pointless in the same stroke — see
`CHANGELOG.md`.

## Commands

```bash
composer install
composer check          # lint + analyse + test, what CI runs
composer test           # phpunit, through Orchestra Testbench
composer analyse        # phpstan level 10 with larastan, PHP 8.3-8.5
composer format         # pint

vendor/bin/phpunit tests/ClientResolutionTest.php        # one file
vendor/bin/phpunit --filter the_facade_resolves          # one test
```

**Core v2 is not published yet**, so `composer install` cannot resolve `hampel/synergy-wholesale`
from Packagist and CI will stay red until it is tagged and pushed. Local installs need a path
repository — `CLAUDE.local.md` has the two lines that do it, and it must not be committed.

## Architecture

The whole package is one binding, and the shape of it is the only real decision here:

```php
$this->app->singleton(Transport::class, static fn (): Transport => SoapTransport::make());

$this->app->singleton(SynergyWholesale::class, function (): SynergyWholesale {
    return SynergyWholesale::with($this->app->make(Transport::class), $resellerId, $apiKey, $logger);
});
```

**`Transport` is bound separately, and the client is built with `with()` rather than `make()`.**
`make()` would construct a `SoapTransport` inside the client and seal it in. `Transport` is the
client's only extension point — `SynergyWholesale` is final and its API classes are generated — so
caching, retries, rate limiting and an application's own test fixtures all attach by decorating
that binding. `ClientResolutionTest::the_transport_binding_can_be_decorated()` is what stops that
regressing.

Everything else follows from it:

- **Credentials are read at resolve time and refused if empty** (`MissingCredentials`, which
  implements the core package's `SynergyWholesaleException` marker). An empty credential otherwise
  reaches the API and returns `ERR_RESELLER_NOT_AUTHORISED` — the same error a correct key returns
  from an IP that is not on the allowlist, so the two get confused exactly when it is most
  expensive.
- **The facade carries `@method` annotations for all ten groups.** Calls are one hop in —
  `SynergyWholesale::domains()->checkDomain(...)` — and without the annotations that hop is untyped
  to the IDE and to PHPStan, which is most of what a facade costs.
- **Config holds `reseller_id` and `api_key` and nothing else**, published under
  `--tag=synergy-wholesale-config`.

### Caching is deliberately absent

Not an oversight and not a porting shortfall: it was dropped when v2 was scoped. If it comes back
it is a `CachingTransport implements Transport` decorator keyed on operation plus request, bound
over the transport above — no per-operation code, and no ability to fabricate a response object,
which is what the old `bulkCheckDomain()` did. Do not reintroduce it by subclassing anything.

## Tests

Orchestra Testbench boots a minimal application from inside the suite, so the Laravel version under
test comes from Composer resolution — never install a framework to test a package. `FixtureTransport`
from the core package replaces the network, so a test can assert on the exact request that would
have gone over the wire, including that configured credentials reached it.

`TestCase::container()` exists because Testbench declares `$app` nullable; use it rather than
`$this->app` or PHPStan flags every line at level 10.

There is no `hampel/rig` harness and there should not be one: what this package owns *is* container
and configuration wiring, which Testbench can see. The undeclared-dependency hazard rig exists to
catch is covered by the `Declared dependencies` CI job, which analyses `src/` with the dev
dependencies uninstalled. See `~/packages/CLAUDE.md`.

## Conventions

PSR-12 via Pint, PHPStan level 10 with Larastan, `declare(strict_types=1)` everywhere, classes
`final` unless something needs to extend them. Tests use PHPUnit attributes (`#[Test]`) and
snake_case method names. This matches the core package, which is the reference for anything not
settled here.

Version support is policy, not preference: PHP `>=8.3` and `illuminate/support ^12.0|^13.0`, per
`/srv/www/version-support.html`. CI tests the three corners of that range — `8.3`/`^12.0` with
`--prefer-lowest`, `8.3`/`^13.0`, `8.5`/`^13.0` — with Testbench pinned alongside each Laravel major
because its majors track Laravel's. Read the policy before widening or narrowing anything.

## Where to look

- `~/packages/synergy-wholesale/CLAUDE.md` — core architecture, the transport seam, the WSDL traps.
- `~/packages/sparkpost-laravel` — the other Laravel package here, and the source of this one's CI.
- `~/cli/sw-cli` — the other consumer, still on 1.x and in worse shape: it declares
  `"name": "laravel/laravel"` and carries a full web skeleton for a dozen CLI commands. Its own
  piece of work, possibly a rebuild as a Laravel Zero app. It was the only caller of `$fresh`.
