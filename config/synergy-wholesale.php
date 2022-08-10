<?php
/**
 * Configuration for Synergy Wholesale
 */

return array(

    /*
    |--------------------------------------------------------------------------
    | Synergy Wholesale API Key
    |--------------------------------------------------------------------------
    |
    | Specify the API Key for your Synergy Wholesale account
    |
    */

    'api_key' => env('SYNERGY_WHOLESALE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Synergy Wholesale Reseller ID
    |--------------------------------------------------------------------------
    |
    | Specify the Reseller ID for your Synergy Wholesale account
    |
    | NOTE: as of Laravel 5.8, all expiry times should be considered to be in seconds rather than minutes
    |
    */

    'reseller_id' => env('SYNERGY_WHOLESALE_RESELLER_ID', ''),

	'cache' => [
		'balanceQuery' => [
			'key' => 'sw.balancequery',
		],

		'bulkCheckDomain' => [
			'key' => 'sw.bulkcheckdomain',
			'expiry' => 60*60*24
		],

		'businessCheckRegistration' => [
			'key' => 'sw.businesscheckregistration',
			'expiry' => 60*60*24*7
		],

		'canRenewDomain' => [
			'key' => 'sw.canrenewdomain',
			'expiry' => 60*60*24
		],

		'checkDomain' => [
			'key' => 'sw.checkdomain',
			'expiry' => 60*60*24
		],

		'domainInfo' => [
			'key' => 'sw.domaininfo',
			'expiry' => 60*60*24
		],

		'getDomainExtensionOptions' => [
			'key' => 'sw.getdomainextensionoptions',
			'expiry' => 60*60*24*7
		],

		'getDomainPricing' => [
			'key' => 'sw.domainpricing',
			'expiry' => 60*60*24
		],

		'getTransferredAwayDomains' => [
			'key' => 'sw.gettransferredawaydomains',
			'expiry' => 60*60*24
		],

		'getUSNexusData' => [
			'key' => 'sw.getusnexusdata',
			'expiry' => 60*60*24
		],

		'listContacts' => [
			'key' => 'sw.listcontacts',
			'expiry' => 60*60*24
		],

	],
);
