<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Folder Protection Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are used by the folder protection middleware to
    | restrict access to your application, typically on staging.
    |
    */

    'enabled' => env('FOLDER_PROTECTION_ENABLED', config('app.env') === 'staging'),
    'user' => env('FOLDER_PROTECTION_USER', env('APP_FOLDER_PROTECTION_USER')),
    'password' => env('FOLDER_PROTECTION_PASSWORD', env('APP_FOLDER_PROTECTION_PASSWORD')),
];
