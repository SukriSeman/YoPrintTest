<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;


Route::get('/', [UploadController::class, 'index'])->name('index');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
Route::get('/uploads', [UploadController::class, 'list'])->name('uploads.list');
