<?php
// 古いルートをリダイレクト

use App\Models\Facility;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

Route::get('s/{service}/{facility}', function ($service, Facility $facility) {
    return to_route('facility', $facility, 308);
})->whereNumber('service')
    ->whereUlid('facility');

Route::get('s/{service}', function (Service $service) {
    return to_route('home', ['service' => $service]);
})->whereNumber('service');

Route::get('f/{facility:wam}', function (Facility $facility) {
    return to_route('facility', $facility, 308);
})->whereAlphaNumeric('facility');
