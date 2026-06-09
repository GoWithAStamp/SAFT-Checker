<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['pt', 'en'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('locale.switch');
