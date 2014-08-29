Synergy Wholesale API Wrapper for Laravel
=========================================

A Synergy Wholesale API wrapper for Laravel 4.x

By [Simon Hampel](http://hampelgroup.com/).

This package provides a simple Laravel service provider and facade for our base Synergy Wholesale API wrapper package
[hampel/linode](https://packagist.org/packages/hampel/synergy-wholesale) - please refer to the documentation about that
package for instructions on how to use this API wrapper -
[hampel/synergy-wholesale on Bitbucket](https://bitbucket.org/hampel/synergy-wholesale)

Installation
------------

The recommended way of installing the Synergy Wholesale package is through [Composer](http://getcomposer.org):

Require the package via Composer in your `composer.json`

    :::json
    {
        "require": {
            "hampel/synergy-wholesale-laravel": "dev-master@dev"
        }
    }

Run Composer to update the new requirement.

    :::bash
    $ composer update

The package is built to work with the Laravel 4 Framework.

Open your Laravel config file `app/config/app.php` and add the service provider in the `$providers` array:

    :::php
    'providers' => array(

        ...

        'SynergyWholesale\Laravel\SynergyWholesaleServiceProvider'

    ),

You may also optionally add an alias entry to the `$aliases` array in the same file for the SynergyWholesale facade:

	:::php
    "aliases" => array(

    	...

    	'SynergyWholesale'			  => 'SynergyWholesale\Laravel\Facades\SynergyWholesale',

    ),

Finally, to utilise the SynergyWholesale API, you must generate an API key using the Synergy Wholesale control panel
(which involves adding you web server's IP address to the whitelist), and then you should specify your reseller key in
the services configuration file `app/config/services.php`:

    :::php
    'synergy-wholesale' => array(
    	'reseller_id' => 'your reseller id',
    	'api_key' => 'your api key',
    ),

Usage
-----

Use Laravel's App facade to gain access to the service provider in your code:

    :::php
    use SynergyWholesale\Commands\CheckDomainCommand;

    $sw = App::make('synergy-wholesale');
    $available = $sw->execute(new CheckDomainCommand(new Domain('example.com')));

... or chain them:

    :::php
    $available = App::make('synergy-wholesale')->execute(new CheckDomainCommand(new Domain('example.com')));

... or just use the Facade instead:

    :::php
    $available = SynergyWholesale::execute(new CheckDomainCommand(new Domain('example.com')));

Refer to the usage examples and code in the
[Synergy Wholesale API Wrapper](https://bitbucket.org/hampel/synergy-wholesale) repository for more details about how
to use the library.
