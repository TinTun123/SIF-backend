<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

<<<<<<< HEAD
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*', 'public/*'],
=======
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'public/*', 'storage/*'],
>>>>>>> 3bd1171764087f7ee3c5a24bd89724a0350f6987

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://frontend.mef-myanmar.com/',
        // 'http://127.0.0.1:4173',
        // 'http://localhost:5173',
        // 'http://127.0.0.1:5173',
        // 'http://localhost:5174',
        // 'http://127.0.0.1:5174',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
