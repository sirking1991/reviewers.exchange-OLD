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
    // Route::get('/publisher/reviewers/list', 'PublisherController@reviewerList')->name('/publisher/reviewers/list');
    Route::get('/publisher/reviewers/{id?}', 'PublisherController@reviewerShow');
    Route::match(['post', 'put'], '/publisher/reviewers/{id?}', 'PublisherController@reviewerSave');    
    Route::get('/publisher/reviewers/{id}/delete', 'PublisherController@reviewerDelete');
    
    Route::match(['post', 'put'], '/publisher/reviewers/{reviewerId}/question/{id?}', 'PublisherController@saveQuestion');
    Route::match(['delete'], '/publisher/reviewers/{reviewerId}/question/{id?}', 'PublisherController@deleteQuestion');
    
    Route::get('/publisher/reviewers/{reviewerId}/questionnaire-groups', 'PublisherController@questionnaireGroups' );
    Route::match(['post', 'put'], '/publisher/reviewers/{reviewerId}/questionnaire-group/{id?}', 'PublisherController@questionnaireGroupSave' );
    Route::match(['delete'], '/publisher/reviewers/{reviewerId}/questionnaire-group/{id}', 'PublisherController@questionnaireGroupDelete' );
    
    Route::livewire('/publisher/reviewer-list', 'publisher-reviewer-list');
    Route::view('/publisher/reviewer-list', 'publisher.reviewer-list');
    Route::livewire('/publisher/statement', 'publisher-statement');

    Route::view('/publisher/settings', 'publisher.settings');

    
    

});

Route::middleware(['auth'])->group(function(){

    Route::get('/generateExam/{reviewerId}', 'ReviewerController@generateExam');
    Route::post('/saveExamResult', 'ReviewerController@saveExamResult');
    Route::get('/userExamSummary/{reviewerId}', 'ReviewerController@userExamSummary');
});

Route::middleware([])->group(function(){
    Route::get('/paymongo/buy-reviewer/{reviewerId}', 'PaymongoController@buyReviewer');
    Route::post('/paymongo/webhook', 'PaymongoController@webhook');
    Route::get('/paymongo/confirm-payment/{clientKey}', 'PaymongoController@confirmPayment');
});


