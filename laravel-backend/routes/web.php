<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return response(File::get(public_path('index.html')), 200)
        ->header('Content-Type', 'text/html');
})->where('any', '^(?!api).*$');
