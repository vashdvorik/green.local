<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/about/project', 'pages.about-project')->name('about.project');
Route::view('/about/mission', 'pages.about-mission')->name('about.mission');
Route::view('/about/directions', 'pages.about-directions')->name('about.directions');
Route::view('/about/audits', 'pages.about-audits')->name('about.audits');
Route::view('/about/results', 'pages.about-results')->name('about.results');
Route::view('/about/reports', 'pages.about-reports')->name('about.reports');
Route::view('/business', 'pages.business')->name('business');
Route::view('/news', 'pages.news')->name('news');
Route::view('/stories', 'pages.stories')->name('stories');
Route::redirect('/media', '/media/photos')->name('media');
Route::view('/media/photos', 'pages.media-photos')->name('media.photos');
Route::view('/media/videos', 'pages.media-videos')->name('media.videos');
Route::view('/media/catalogues', 'pages.media-catalogues')->name('media.catalogues');
Route::view('/partners', 'pages.partners')->name('partners');
Route::view('/contacts', 'pages.contacts')->name('contacts');
