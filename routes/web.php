<?php

use Illuminate\Support\Facades\Route;
use Sanlilin\LaravelTheme\Http\Controllers\LaravelThemeController;

Route::group(['as' => 'seller.', 'prefix' => 'seller', 'middleware' => ['auth','seller'],], function () {
	Route::group(['as' =>'theme.','prefix'=>'theme'], function () {
		Route::get('/list',[LaravelThemeController::class,'list'])->name('list');
		Route::get('/market',[LaravelThemeController::class,'market'])->name('market');
		Route::post('/disable',[LaravelThemeController::class,'disable'])->name('disable');
		Route::post('/enable',[LaravelThemeController::class,'enable'])->name('enable');
		Route::post('/delete',[LaravelThemeController::class,'delete'])->name('delete');
		Route::post('/batch',[LaravelThemeController::class,'batch'])->name('batch');
		Route::any('/install',[LaravelThemeController::class,'install'])->name('install');
		Route::any('/publish',[LaravelThemeController::class,'publish'])->name('publish');
		Route::any('/register',[LaravelThemeController::class,'register'])->name('register');
		Route::any('/login',[LaravelThemeController::class,'login'])->name('login');
		Route::any('/upload',[LaravelThemeController::class,'upload'])->name('upload');
		Route::any('/download',[LaravelThemeController::class,'download'])->name('download');
	});
});