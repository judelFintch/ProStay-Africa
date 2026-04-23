<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('customers', 'pages.customers')->name('customers.index');
    Route::view('orders', 'pages.orders')->name('orders.create');
    Route::view('billing/invoices', 'pages.billing')->name('billing.invoices');
    Route::view('pos', 'pages.pos')->name('pos.quick-sale');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
