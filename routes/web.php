<?php

use App\Http\Controllers\SitemapController;
use App\Livewire\Home;
use App\Models\Facility;
use App\Models\Service;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', Home::class)->name('home');

Volt::route('s/{service}/{facility}', 'facility')
    ->name('facility')
    ->whereNumber('service')
    ->whereUlid('facility');

Route::get('s/{service}', function (Service $service) {
    return to_route('home', ['service' => $service]);
})->whereNumber('service');

Route::get('f/{facility:wam}', function (Facility $facility) {
    return to_route('facility', ['service' => $facility->service, 'facility' => $facility], 308);
})->whereAlphaNumeric('facility');

Volt::route('c/{company}', 'company')->name('company');

Volt::route('contact', 'contact')->name('contact');
Volt::route('map', 'map')->name('map');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

//Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
