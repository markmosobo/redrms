<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app'); // or welcome, or whatever mounts Vue
})->where('any', '.*');
