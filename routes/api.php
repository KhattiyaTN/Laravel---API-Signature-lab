<?php

use Illuminate\Support\Facades\Route;

Route::post('/lab1/test', function () {
    return response()->json(['message' => 'Signature valid']);
});