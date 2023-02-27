<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UcpController;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth::routes(['verify' => true]);



Route::get('home', [AuthController::class, 'home'])->middleware(['auth', 'is_verify_email'])->name('home');

Route::get('account/verify', [AuthController::class, 'verify'])->middleware(['auth'])->name('verifyAccount');
// Route::get('account/status', [AuthController::class, 'verifyPage'])->middleware(['auth'])->name('verifyPage');
Route::get('account/verify/token/{token}', [AuthController::class, 'verifyAccount'])->middleware(['auth'])->name('verifyUser');
Route::get('account/resend/verify', [AuthController::class, 'sendEmailVerification'])->middleware(['auth'])->name('sendVerify');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');



Route::group(['middleware' => ['guest']], function(){
    Route::get('login', [AuthController::class, 'index'])->name('login');
    Route::post('actionLogin', [AuthController::class, 'actionLogin'])->name('actionLogin');

    Route::get('register', [AuthController::class, 'register'])->name('register');
    Route::post('actionRegister', [AuthController::class, 'actionRegister'])->name('actionRegister');
});

Route::get('forgot-password', [AuthController::class, 'forgotpassword'])->name('forgotpassword');
Route::post('forgot-password', [AuthController::class, 'actionForgotPassword'])->name('actionforgotpassword');


