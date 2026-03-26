<?php

return [
    /*
    * This string is used the to generate a signature. You should
    * keep this value secret. Defaults to APP_KEY.
    */
    'signature_key' => env('URL_SIGNER_SIGNATURE_KEY', env('APP_KEY')),

    /*
     * The default expiration time of a URL in seconds.
     */
    'default_expiration_time_in_seconds' => 60 * 60 * 24,

    /*
     * These strings are used a parameter names in a signed url.
     */
    'parameters' => [
        'expires' => 'expires',
        'signature' => 'signature',
    ],
];
