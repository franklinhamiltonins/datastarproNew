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

    'paths' => ['*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    // 'allowed_origins' => ['*'],
    'allowed_origins' => ['http://localhost:5173','https://datastarpro-pipedrive.vercel.app','http://18.189.66.130','https://pipelinedrive.datastarpro.com','http://pipelinedrive.datastarpro.com','https://18.189.66.130'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With', '*'],

    'exposed_headers' => false,

    'max_age' => false,

    'supports_credentials' => true,

    // 'paths' => ['*'],

    // 'allowed_methods' => ['*'],

    // 'allowed_origins' => ['*'], // Your React app's URL

    // 'allowed_origins_patterns' => [],

    // 'allowed_headers' => ['*'],

    // 'exposed_headers' => [],

    // 'max_age' => 0,

    // 'supports_credentials' => true, // Allow credentials (cookies) to be sent

];
