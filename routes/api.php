<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuestionController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::controller(UserController::class)->group(function () {
    Route::post('/signup', 'signUp');
    Route::post('/login', 'logIn');
    Route::get('/autherror', 'authError')->name('authError');
});

Route::controller(QuestionController::class)->group(function () {
    Route::post('/questions', 'askQuestion');
    Route::get('/questions', 'getQuestions');
});
