<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'app');

Route::redirect('/login', '/logi-sisse');

Route::view('/{any}', 'app')
    ->where('any', '^(?!api).*$');
