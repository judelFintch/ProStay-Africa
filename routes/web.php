<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['fr', 'en'], true), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('customers', 'pages.customers')->name('customers.index');
    Route::view('customers/registry', 'pages.customers-registry')->name('customers.registry');
    Route::view('reservations', 'pages.reservations')->name('reservations.index');
    Route::view('rooms', 'pages.rooms')->name('rooms.index');
    Route::view('orders', 'pages.orders')->name('orders.create');
    Route::view('billing/invoices', 'pages.billing')->name('billing.invoices');
    Route::view('billing/payments', 'pages.payments')->name('billing.payments');
    Route::view('stock', 'pages.stock')->name('stock.index');
    Route::view('laundry', 'pages.laundry')->name('laundry.index');
    Route::view('pos', 'pages.pos')->name('pos.quick-sale');
    Route::view('reports', 'pages.reports')->name('reports.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
