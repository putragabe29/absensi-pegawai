<?php

use Illuminate\Support\Str;

return [

    'driver' => 'file',

    'lifetime' => 120,

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'connection' => null,

    'table' => 'sessions',

    'store' => null,

    'lottery' => [2, 100],

    'cookie' => Str::slug(env('APP_NAME', 'absensi-kpu')).'-session',

    'path' => '/',

    'domain' => null,

    // ⚠️ WAJIB FALSE UNTUK WEBVIEW
    'secure' => false,

    'http_only' => true,

    // ⚠️ JANGAN "none" KARENA WEBVIEW AKAN BLOK
    'same_site' => 'lax',

    'partitioned' => false,
];
