<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/receipts/{receipt}/print', [ReceiptController::class, 'print']);

Route::get('/{any}', function () {
    return view('app'); // or welcome, or whatever mounts Vue
})->where('any', '.*');

