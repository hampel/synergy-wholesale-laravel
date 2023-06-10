Synergy Wholesale API Wrapper for Laravel
=========================================

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://packagist.org/packages/hampel/synergy-wholesale-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://packagist.org/packages/hampel/synergy-wholesale-laravel)
[![Open Issues](https://img.shields.io/bitbucket/issues/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://bitbucket.org/hampel/synergy-wholesale-laravel/issues)
[![License](https://img.shields.io/packagist/l/hampel/synergy-wholesale-laravel.svg?style=flat-square)](https://packagist.org/packages/hampel/synergy-wholesale-laravel)

Synergy Wholesale API wrapper using SoapClient and packaged as a Laravel service provider

By [Simon Hampel](https://twitter.com/SimonHampel).

This package provides a simple Laravel service provider and facade for our base Synergy Wholesale API wrapper package
[hampel/linode](https://bitbucket.org/hampel/synergy-wholesale) - please refer to the documentation about that
package for instructions on how to use this API wrapper

Installation
------------

The recommended way of installing the Synergy Wholesale package is through [Composer](http://getcomposer.org):

	:::bash
	composer require "hampel/synergy-wholesale-laravel"

Or manually define it in your `composer.json`

    :::json
    {
        "require": {
            "hampel/synergy-wholesale-laravel": "^1.6"
        }
    }

Run Composer to update the new requirement.

    :::bash
    $ composer update

Finally, to utilise the SynergyWholesale API, you must generate an API key using the Synergy Wholesale control panel 
(which involves adding your web server's IP address to the whitelist) and then specify that key and your reseller ID
in your `.env` file:

    :::bash
    SYNERGY_WHOLESALE_API_KEY=your_synergy_wholesale_api_key
    SYNERGY_WHOLESALE_RESELLER_ID=your_synergy_wholesale_reseller_id

Upgrading
---------

__Upgrading to v1.6 (Laravel v5.8)__

In line with changes made in Laravel v5.8, cache expiry times are now specified in seconds rather than minutes. Be sure
to adjust the value of all cache entries in the config`synergy-wholesale.cache.*.expiry` to suit, if you have 
over-ridden the defaults.

Usage
-----

Use Laravel's App facade to gain access to the service provider in your code:

    :::php
    use SynergyWholesale\Commands\CheckDomainCommand;

    $sw = App::make('SynergyWholesale\SynergyWholesale');
    $available = $sw->execute(new CheckDomainCommand(new Domain('example.com')));

... or chain them:

    :::php
    $available = App::make('SynergyWholesale\SynergyWholesale')->execute(new CheckDomainCommand(new Domain('example.com')));

... or just use the Facade instead:

    :::php
    $available = SynergyWholesale::execute(new CheckDomainCommand(new Domain('example.com')));

Refer to the usage examples and code in the
[Synergy Wholesale API Wrapper](https://bitbucket.org/hampel/synergy-wholesale) repository for more details about how
to use the library.
