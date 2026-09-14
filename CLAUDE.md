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

## Architecture

The whole package is one binding, and the shape of it is the only real decision here:

```php
$this->app->singletonIf(Transport::class, static fn (): Transport => SoapTransport::make());

$this->app->singletonIf(SynergyWholesale::class, function (): SynergyWholesale {
    return SynergyWholesale::with($this->app->make(Transport::class), $resellerId, $apiKey, $logger);
});
```

**`Transport` is bound separately, and the client is built with `with()` rather than `make()`.**
`make()` would construct a `SoapTransport` inside the client and seal it in. `Transport` is the
client's only extension point — `SynergyWholesale` is final and its API classes are generated — so
caching, retries, rate limiting and an application's own test fixtures all attach by decorating
that binding. `ClientResolutionTest::the_transport_binding_can_be_decorated()` is what stops that
regressing.

**Both are `singletonIf()`, not `singleton()`, so a binding the application made first is kept.**
Laravel Zero runs no package discovery and lists the application's own provider above any package
provider added after it, so an application's replacement transport or client is usually bound
before this provider registers, and `singleton()` would replace it without a word. `extend()` is
unaffected either way. The two `..._the_application_bound_first_is_kept` tests in
`ConfigurationTest` fail against `singleton()`.

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
catch is covered by the `Declared dependencies` CI job, in two steps against a `--no-dev` install.
PHPStan over `src/` finds a symbol that only `require-dev` supplied. **It cannot find one that is
installed without being declared**: Composer satisfies `illuminate/support` with the whole of
`laravel/framework`, so `config_path()` from the undeclared foundation component resolved, and went
unnoticed with that step green. `composer-require-checker` follows, mapping each symbol to the
package that supplies it. Its whitelist, `.github/composer-require-checker.json`, holds only
`illuminate/*` symbols that can never resolve because `laravel/framework` replaces those components;
a new entry means checking the component is in `require` first. The workflow's comments carry the
detail.

## Conventions

PSR-12 via Pint, PHPStan level 10 with Larastan, `declare(strict_types=1)` everywhere, classes
`final` unless something needs to extend them. Tests use PHPUnit attributes (`#[Test]`) and
snake_case method names. This matches the core package, which is the reference for anything not
settled here.

Version support is policy, not preference: PHP `>=8.3` and `illuminate/support ^12.0|^13.0`. The
package supports every PHP version with upstream security support, and the current Laravel major
plus one back. CI tests the three corners of that range — `8.3`/`^12.0` with `--prefer-lowest`,
`8.3`/`^13.0`, `8.5`/`^13.0` — with Testbench pinned alongside each Laravel major because its
majors track Laravel's. Widening or narrowing a constraint is a policy decision, not a
convenience: raise it with the maintainer first.

## Where to look

- [`hampel/synergy-wholesale`](https://github.com/hampel/synergy-wholesale) — the client itself.
  Its `CLAUDE.md` covers the architecture, the transport seam and the WSDL traps behind the
  generated code, and is where any question about what an operation does belongs.
- `CHANGELOG.md` — what 2.0 changed and why 1.x worked the way it did.
