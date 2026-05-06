<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/logi-sisse');
Route::redirect('/login', '/logi-sisse');

Route::view('/{any}', 'app')
    ->where('any', '^(?!api).*$');
