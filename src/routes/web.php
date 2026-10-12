<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\SitemapController;
use Modules\Media\Controllers\MediaImageController;
use Modules\Media\Helpers\FileHelper;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/media/{id}/{size}', [MediaImageController::class, 'show'])
    ->whereNumber('id')
    ->whereIn('size', array_keys(FileHelper::$defaultSize))
    ->name('media.image');
