<?php

use Illuminate\Support\Facades\Route;
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



Auth::routes();

Route::get('/', 'HomeController@index');

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['checkifpublisher'])->group(function () {
    Route::get('/publisher/reviewers/list', 'PublisherController@reviewerList')->name('/publisher/reviewers/list');
    Route::get('/publisher/reviewers/{id?}', 'PublisherController@reviewerShow');
    Route::match(['post', 'put'], '/publisher/reviewers/{id?}', 'PublisherController@reviewerSave');    
    Route::get('/publisher/reviewers/{id}/delete', 'PublisherController@reviewerDelete');
    
    Route::match(['post', 'put'], '/publisher/reviewers/{reviewerId}/question/{id?}', 'PublisherController@saveQuestion');
    Route::match(['delete'], '/publisher/reviewers/{reviewerId}/question/{id?}', 'PublisherController@deleteQuestion');
    
    Route::get('/publisher/reviewers/{reviewerId}/questionnaire-groups', 'PublisherController@questionnaireGroups' );
    Route::match(['post', 'put'], '/publisher/reviewers/{reviewerId}/questionnaire-group/{id?}', 'PublisherController@questionnaireGroupSave' );
    Route::match(['delete'], '/publisher/reviewers/{reviewerId}/questionnaire-group/{id}', 'PublisherController@questionnaireGroupDelete' );
    
});

Route::middleware(['auth'])->group(function(){

    Route::get('/generateExam/{reviewerId}', 'ReviewerController@generateExam');
    Route::post('/saveExamResult', 'ReviewerController@saveExamResult');
    Route::get('/userExamSummary/{reviewerId}', 'ReviewerController@userExamSummary');
    Route::get('/buyReviewer/{reviewerId}', 'ReviewerController@buyReviewer');

});
Route::middleware([])->group(function(){
    Route::get('/paymaya/checkout', 'PaymayaController@checkout')->name('paymaya-checkout');
    Route::post('/paymaya/callback/{status}', 'PaymayaController@callback');
    Route::get('/paymaya/redirectUrl/{status}/{reference}', 'PaymayaController@redirect');
});


