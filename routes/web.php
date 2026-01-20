<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin panel route - serve the React admin interface
Route::get('/admin', function () {
    return view('admin.dashboard');
});

// Alternative admin route (same as /admin)
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});
