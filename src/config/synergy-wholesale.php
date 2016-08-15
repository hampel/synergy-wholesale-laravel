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
    */

    'reseller_id' => env('SYNERGY_WHOLESALE_RESELLER_ID', ''),

	'cache' => [
		'balanceQuery' => [
			'key' => 'sw.balancequery',
		],

		'bulkCheckDomain' => [
			'key' => 'sw.bulkcheckdomain',
			'expiry' => 1440
		],

		'businessCheckRegistration' => [
			'key' => 'sw.businesscheckregistration',
			'expiry' => 10080
		],

		'canRenewDomain' => [
			'key' => 'sw.canrenewdomain',
			'expiry' => 1440
		],

		'checkDomain' => [
			'key' => 'sw.checkdomain',
			'expiry' => 1440
		],

		'domainInfo' => [
			'key' => 'sw.domaininfo',
			'expiry' => 1440
		],

		'getDomainExtensionOptions' => [
			'key' => 'sw.getdomainextensionoptions',
			'expiry' => 10080
		],

		'getDomainPricing' => [
			'key' => 'sw.domainpricing',
			'expiry' => 1440
		],

		'getTransferredAwayDomains' => [
			'key' => 'sw.gettransferredawaydomains',
			'expiry' => 1440
		],

		'getUSNexusData' => [
			'key' => 'sw.getusnexusdata',
			'expiry' => 1440
		],

		'listContacts' => [
			'key' => 'sw.listcontacts',
			'expiry' => 1440
		],

	],
);
