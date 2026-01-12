<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://pureclean-admin.botumsakor.com', 'http://localhost:5000', 'http://localhost:3000'], // ✅ YOUR FRONTEND DOMAIN

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ✅ Important
];
