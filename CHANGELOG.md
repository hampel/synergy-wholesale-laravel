CHANGELOG
=========

2.0.0 (unreleased)
------------------

A complete rewrite for `hampel/synergy-wholesale` v2, which shares no API with 1.x. Nothing from
the 1.x version of this package survives — see the upgrade table in the README.

### What the package is now

* Namespaced `Hampel\SynergyWholesale\Laravel\`, so it no longer shares a root namespace with the
  core package.
* The container binds `Hampel\SynergyWholesale\SynergyWholesale` as a singleton, built with
  `with()` over a separately bound `Transport`. Operations are reached through groups:
  `SynergyWholesale::domains()->checkDomain(domainName: 'example.com')`.
* The facade carries `@method` annotations for all ten operation groups, so calls through it are
  typed to both the IDE and PHPStan.
* Resolving a client without a reseller id or API key throws `MissingCredentials` rather than
  letting an empty credential reach the API, where it is indistinguishable from calling from an
  address that is not on the allowlist.
* Config publishes under `--tag=synergy-wholesale-config`, and holds only `reseller_id` and
  `api_key`.

### Caching is gone

* `CachingSynergyWholesale` is deleted, along with the whole `cache` config block and the `$fresh`
  parameter that sat on the public signature of every read.
* It could not survive v2 in any case: `SynergyWholesale` is final and holds no operation methods
  to override, and the design needed one override per cached operation — workable at 36 operations,
  not at 138, where an operation with no override silently bypassed the cache.
* Caching now belongs in a `Transport` decorator, which needs no per-operation code and cannot
  fabricate a response object — which `bulkCheckDomain()` did, hand-building a synthetic `stdClass`
  with `status = "OK"` to satisfy a constructor.

### Supported versions, and the tooling to back them

* PHP 8.3 or later, Laravel 12 or 13. Was PHP 7.3 and Laravel 8 to 10, all out of support.
* Adds the tests, static analysis and CI the package never had: PHPUnit through Orchestra
  Testbench, PHPStan level 10 with Larastan across PHP 8.3 to 8.5, Pint, and a CI matrix at the
  corners of the supported range.
* Forces LF line endings via `.gitattributes`, and keeps development-only files out of the
  distributed archive.

1.10.0 (2023-06-05)
------------------

* works with Laravel 10.x

1.9.0 (2022-08-10)
------------------

* works with Laravel 9.x

1.8.0 (2020-09-17)
------------------

* works with Laravel 8.x

1.7.0 (2020-06-16)
------------------

* works with Laravel 7.x

1.6.1 (2019-10-14)
------------------

* works with Laravel 6.x

1.6.0 (2019-03-28)
------------------

* publish the config in the service provider
* add support for Laravel v5.8
* change all the default expiry times to seconds in line with Laravel 5.8 changes

1.5.0 (2019-01-23)
------------------

* updates for Laravel 5.7

1.3.1 (2016-08-15)
------------------

* bugfix in CachingSynergyWholesale::bulkCheckDomain

1.3.0 (2016-08-15)
------------------

* added caching to SynergyWholesaleServiceProvider

1.2.1 (2016-08-07)
------------------

* shouldn't be using deferred service provider

1.2.0 (2016-08-07)
------------------

* changed facade to use classname
* made more use of auto dependency injection in service provider binding 
* updated documentation

1.1.0 (2016-08-07)
------------------

* updated for Laravel 5.2

1.0.1 (2015-05-22)
------------------

* removed redundant closing php tags

1.0.0 (2015-02-13)
------------------

* service provider now Laravel 5 compatible
* moved to using .env files for configuration
* removed unnecessary Laravel from namespace
* updated composer.json to use new 1.0 release of hampel/synergy-wholesale
* updated namespaceing
* updated branch-alias

0.5.0 (2014-11-27)
------------------

* updated requirements for hampel/synergy-wholesale to ~0.5

0.4.1 (2014-10-15)
------------------

* updated requirement version for hampel/synergy-wholesale package

0.4.0 (2014-09-26)
------------------

* updated requirements to Laravel v5.0
* updated minimum PHP version to 5.4.0
* changed dev-master alias to 0.4.x
* changed minimin-stability to dev to work with Laravel v5.0 pre-release
* service provider tweaks

0.3.1 (2014-09-26)
------------------

* updated branch-alias to use 0.3.x as dev-master

0.3.0 (2014-08-31)
------------------

* updated requirements to use version 0.3.0 of SynergyWholesale class

0.2.0 (2014-08-29)
------------------

* namespace change to fit in with hampel/synergy-wholesale changes
* implement new constructor for SynergyWholesale

0.1.0 (2014-08-07)
------------------

* initial release
