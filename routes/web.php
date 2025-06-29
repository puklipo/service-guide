<?php

use App\Http\Controllers\SitemapController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', Home::class)->name('home');

Volt::route('s/{facility}', 'facility')
    ->name('facility')
    ->whereUlid('facility');

Volt::route('c/{company}', 'company')
    ->name('company')
    ->whereNumber('company');

Volt::route('articles/{date}/{slug}', 'articles.show')
    ->name('articles.show')
    ->where('date', '[0-9]{4}[0-9]{2}');

Volt::route('map', 'map')->name('map');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

require __DIR__.'/redirect.php';

Route::fallback(function () {
    return to_route('home');
});
