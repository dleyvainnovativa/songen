<?php

/*
|------------------------------------------------------------------------------
| Configuración Firebase
|------------------------------------------------------------------------------
|
| Lado servidor (kreait): ruta al JSON de la cuenta de servicio para verificar
| ID tokens. NO lo subas al repo; vive fuera de control de versiones y se
| referencia por ruta en el .env.
|
| Lado cliente: estas llaves son públicas por diseño (van al bundle del navegador
| vía <meta> en el layout). La seguridad real está en la verificación del token
| en el servidor.
|
*/

return [

    // Ruta al service-account JSON (kreait, lado servidor)
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json')),

    // Config pública del cliente web
    'client' => [
        'api_key'        => env('FIREBASE_API_KEY'),
        'auth_domain'    => env('FIREBASE_AUTH_DOMAIN'),
        'project_id'     => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'sender_id'      => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id'         => env('FIREBASE_APP_ID'),
    ],

];
