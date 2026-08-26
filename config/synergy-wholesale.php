<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Synergy Wholesale API Key
    |--------------------------------------------------------------------------
    |
    | Generated in the Synergy Wholesale control panel. The API authorises by IP
    | address as well as by key, so the address of every machine that calls it has
    | to be on the allowlist there -- a correct key from an unlisted address fails
    | with ERR_RESELLER_NOT_AUTHORISED, which reads like a bad key.
    |
    */

    'api_key' => env('SYNERGY_WHOLESALE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Synergy Wholesale Reseller ID
    |--------------------------------------------------------------------------
    |
    | The reseller account the API key belongs to.
    |
    */

    'reseller_id' => env('SYNERGY_WHOLESALE_RESELLER_ID', ''),

];
