<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // En desarrollo puedes dejar '*'. En producción pon tu dominio.
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // Necesario para SPA con Sanctum (cookies/sesión)
    'supports_credentials' => true,

    'exposed_headers' => [],

    'max_age' => 0,

];
