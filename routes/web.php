<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'status' => 'active',
        'endpoints' => [
            'documentation' => '/docs',
            'api' => '/api',
            'health' => '/up',
        ],
    ]);
});
