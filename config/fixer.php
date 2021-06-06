<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Url
    |--------------------------------------------------------------------------
    |
    | This URL is used by the Fixer controller to set the base url for the
    | cURL call. You should set this to the latest url from the API service.
    |
    */

    'base_url' => env('FIXER_API_BASE_URL', 'http://data.fixer.io/api/'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the cURL call for the Fixer api and should set in
    | your .env.
    |
    */

    'api_key' => env('FIXER_API_KEY')
];
