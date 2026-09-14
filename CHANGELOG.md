# CHANGELOG

## 2.1.1 (2026-09-14)

### Fixed

* The service provider keeps a `Transport` or `SynergyWholesale` binding the application registered
  before it. Under Laravel Zero, where the application's provider is listed first, the package's
  binding replaced the application's.

### Documentation

* The README's Laravel Zero section covers publishing the config.

## 2.1.0 (2026-09-14)

### Fixed

* Requires `hampel/synergy-wholesale` `^2.1`, which redacts secrets from the debug log at any
  depth. With 2.0.x, `listDomains` and `bulkDomainInfo` logged the EPP code of every domain in the
  response, despite the README saying EPP codes were redacted.

### Documentation

* The README covers Laravel Zero: the service provider has to be listed in `config/app.php`, and
  the client belongs in a command's `handle()` rather than its constructor, where resolving it
  without credentials breaks `list` and `--help`.

### Development

* CI checks declared dependencies with `composer-require-checker`.
* A test boots the service provider, so a deprecation raised in `boot()` fails the suite.

## 2.0.1 (2026-08-27)

* The config file is published through `Illuminate\Contracts\Foundation\Application::configPath()`
  rather than the `config_path()` helper, which is defined by `illuminate/foundation` — a package
  this one does not require. The published path is unchanged.
* Raised the `larastan/larastan` development requirement to `^3.4.2`, the first version that types
  container resolution. Development only; nothing a consumer installs is affected.

## 2.0.0 (2026-08-27)

A complete rewrite for `hampel/synergy-wholesale` v2. No API is shared with 1.x — the README has
an upgrade table.

* Namespace is now `Hampel\SynergyWholesale\Laravel\`.
* The container binds `Hampel\SynergyWholesale\SynergyWholesale` as a singleton, and
  `Hampel\SynergyWholesale\Transport\Transport` separately. Decorating the transport binding
  applies to the registered client.
* Operations are reached through groups:
  `SynergyWholesale::domains()->checkDomain(domainName: 'example.com')`.
* The facade declares `@method` annotations for all ten operation groups.
* Resolving a client without a reseller id or API key throws `MissingCredentials`, which
  implements `Hampel\SynergyWholesale\Exception\SynergyWholesaleException`.
* Removed `CachingSynergyWholesale`, the `cache` config block, and the `$fresh` parameter on
  every read method. Nothing is cached.
* Config holds `reseller_id` and `api_key`, and publishes under `--tag=synergy-wholesale-config`
  in place of `--tag=config`.
* Minimum PHP version is now 8.3, and Laravel 12 or 13 is required.
* Added a test suite on Orchestra Testbench, PHPStan level 10 with Larastan, Pint, and CI across
  the supported PHP and Laravel range.
* Line endings are enforced as LF, and development-only files are excluded from the distributed
  archive.

## 1.10.0 (2023-06-05)

* works with Laravel 10.x

## 1.9.0 (2022-08-10)

* works with Laravel 9.x

## 1.8.0 (2020-09-17)

* works with Laravel 8.x

## 1.7.0 (2020-06-16)

* works with Laravel 7.x

## 1.6.1 (2019-10-14)

* works with Laravel 6.x

## 1.6.0 (2019-03-28)

* publish the config in the service provider
* add support for Laravel v5.8
* change all the default expiry times to seconds in line with Laravel 5.8 changes

## 1.5.0 (2019-01-23)

* updates for Laravel 5.7

## 1.3.1 (2016-08-15)

* bugfix in CachingSynergyWholesale::bulkCheckDomain

## 1.3.0 (2016-08-15)

* added caching to SynergyWholesaleServiceProvider

## 1.2.1 (2016-08-07)

* shouldn't be using deferred service provider

## 1.2.0 (2016-08-07)

* changed facade to use classname
* made more use of auto dependency injection in service provider binding 
* updated documentation

## 1.1.0 (2016-08-07)

* updated for Laravel 5.2

## 1.0.1 (2015-05-22)

* removed redundant closing php tags

## 1.0.0 (2015-02-13)

* service provider now Laravel 5 compatible
* moved to using .env files for configuration
* removed unnecessary Laravel from namespace
* updated composer.json to use new 1.0 release of hampel/synergy-wholesale
* updated namespaceing
* updated branch-alias

## 0.5.0 (2014-11-27)

* updated requirements for hampel/synergy-wholesale to ~0.5

## 0.4.1 (2014-10-15)

* updated requirement version for hampel/synergy-wholesale package

## 0.4.0 (2014-09-26)

* updated requirements to Laravel v5.0
* updated minimum PHP version to 5.4.0
* changed dev-master alias to 0.4.x
* changed minimin-stability to dev to work with Laravel v5.0 pre-release
* service provider tweaks

## 0.3.1 (2014-09-26)

* updated branch-alias to use 0.3.x as dev-master

## 0.3.0 (2014-08-31)

* updated requirements to use version 0.3.0 of SynergyWholesale class

## 0.2.0 (2014-08-29)

* namespace change to fit in with hampel/synergy-wholesale changes
* implement new constructor for SynergyWholesale

## 0.1.0 (2014-08-07)

* initial release
