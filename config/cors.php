<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for Cross-Origin Resource Sharing
    | or "CORS". This determines which domains are allowed to access your
    | application's resources via JavaScript in a browser.
    |
    | IMPORTANT: For development, '*' can be convenient, but for production,
    |            always specify exact origins for security.
    |
    */

    // Define the paths that should have CORS headers applied.
    // 'api/*' is typically included for API routes. 'sanctum/csrf-cookie' is
    // important if you're using Laravel Sanctum for CSRF token retrieval.
    // 'paths' => ['api/*', 'sanctum/csrf-cookie', 'logout'], // Added 'logout' path explicitly
    'paths' => ['*'],

    // Define which HTTP methods are allowed for cross-origin requests.
    // '*' allows all methods. Ensure 'OPTIONS' is included for preflight requests.
    'allowed_methods' => ['*'], // ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']

    // Define which origins are allowed to make cross-origin requests.
    // This is the most crucial setting for your current error.
    // Option 1 (Recommended for production): List specific origins.
    // Both localhost and 127.0.0.1 on the same port are considered different origins.
    'allowed_origins' => [
        'http://127.0.0.1:8000', // Your frontend is running from here
        'http://localhost:8000',  // Your Laravel backend is listening here
        // Add other frontend domains here if applicable, e.g., 'https://your-production-domain.com'
    ],
    // Option 2 (For development, less secure): Use a wildcard to allow all origins.
    // 'allowed_origins' => ['*'],

    // Define patterns for allowed origins (e.g., for subdomains).
    // This uses regular expressions. Leave empty if not needed.
    'allowed_origins_patterns' => [],

    // Define which headers are allowed in cross-origin requests.
    // '*' allows all headers. Common headers include 'Content-Type', 'Accept',
    // 'X-CSRF-TOKEN' (for Laravel's CSRF protection), and 'Authorization'.
    // If you're sending custom headers from your frontend, list them here.
    'allowed_headers' => ['*'], // ['Content-Type', 'X-Requested-With', 'Accept', 'X-CSRF-TOKEN', 'Authorization']

    // Define which response headers are exposed to the client.
    // By default, only a few headers are exposed. If your API sends custom
    // headers that your frontend needs to read, list them here.
    'exposed_headers' => [],

    // Define how long the results of a preflight request (OPTIONS) can be cached by the browser.
    // This reduces the number of preflight requests. Value is in seconds.
    'max_age' => 0, // 2592000 (30 days) is a common value

    // Indicates whether or not the browser should include credentials (cookies, HTTP authentication)
    // in the cross-origin request. Set to 'true' if your API relies on sessions/cookies
    // for authentication (which Laravel usually does by default).
    'supports_credentials' => true,

];