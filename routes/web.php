<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/business', 'pages.business')->name('business');
Route::view('/news', 'pages.news')->name('news');
Route::view('/stories', 'pages.stories')->name('stories');
Route::view('/media', 'pages.media')->name('media');
Route::view('/partners', 'pages.partners')->name('partners');
Route::view('/contacts', 'pages.contacts')->name('contacts');
