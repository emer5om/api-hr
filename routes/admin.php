<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;

Route::post('/login', [AdminLoginController::class, 'store'])->name('admin.login');