<?php

return [



    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:3000',
        'https://cms-4gva15tu5-ramis-projects-4669c92d.vercel.app',
        'https://living-heddi-cmsbackend-6f6751c2.koyeb.app',
        'https://cms-f.vercel.app/'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
