<?php

use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\InstitutionalController;
use App\Http\Controllers\Store\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/produtos', 'store.products')->name('store.products');
Route::get('/produto/{slug}', ProductController::class)->name('store.product');
Route::get('/quem-somos', [InstitutionalController::class, 'about'])->name('store.about');
Route::get('/contato', [InstitutionalController::class, 'contact'])->name('store.contact');
