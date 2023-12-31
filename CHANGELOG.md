CHANGELOG
=========

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
