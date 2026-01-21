<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://test-pureclean.vercel.app',
                         'http://localhost:3000', 
                         'https://pat-premethodical-kathrine.ngrok-free.dev',
                         'http://localhost:5000'], 
                         

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ✅ Important
];
