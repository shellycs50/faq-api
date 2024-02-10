<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuestionAnswerPairController;
use App\Http\Controllers\PostAuthUserController;
use App\Http\Controllers\TagController;
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
    Route::get('/logout', 'logOut');
});

Route::controller(PostAuthUserController::class)->group(function () {
    Route::get('/validateadmin', 'validateTrainerOrAdminRoute');
});

Route::controller(QuestionAnswerPairController::class)->group(function () {
    Route::post('/student/faq', 'askQuestion'); //required: question, language_id, topic_id
    Route::get('/student/faq', 'getAnswers'); //optional: searchtokens (string json)
    // --
    Route::get('/trainer/faq', 'getUnanswered');
    Route::put('/trainer/faq', 'answerQuestion'); //required: qap_id, answer | optional: question_rename
    Route::get('/trainer/faq/{qap_id}', 'getExistingAnswer'); //required: qap_id
    Route::post('/trainer/faq', 'postQuestionAndAnswer'); // required: question, language_id, topic_id, answer
    Route::delete('/trainer/faq/{qap_id}', 'deleteQuestionAndAnswer'); //required: qap_id
});

Route::controller(TagController::class)->group(function () {
    Route::get('/languages', 'getLanguages');
    Route::post('/languages', 'addLanguage'); //required: name
    Route::get('/topics', 'getTopics'); //required: language_id
    Route::post('/topics', 'addTopic'); //required: name, language_id

});
