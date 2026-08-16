<?php

use App\Http\Controllers\NewsController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PhotoAlbumController;
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
Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/news/{news:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/stories', [OpportunityController::class, 'index'])->name('stories');
Route::get('/stories/{opportunity:slug}', [OpportunityController::class, 'show'])->name('stories.show');
Route::redirect('/media', '/media/photos')->name('media');
Route::get('/media/photos', [PhotoAlbumController::class, 'index'])->name('media.photos');
Route::get('/media/photos/{album:slug}', [PhotoAlbumController::class, 'show'])->name('media.photos.show');
Route::view('/media/videos', 'pages.media-videos')->name('media.videos');
Route::view('/media/catalogues', 'pages.media-catalogues')->name('media.catalogues');
Route::view('/partners', 'pages.partners')->name('partners');
Route::view('/contacts', 'pages.contacts')->name('contacts');
