<?php

use Illuminate\Support\Facades\Route;

// Halaman utama (root /)
Route::get('/', function () {
    return view('welcome');
});

// Halaman tentang (/tentang)
Route::get('/tentang', function () {
    return view('tentang');
});
