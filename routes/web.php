<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/', 'HomeController@index');

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['checkifpublisher'])->group(function () {
    // Route::get('/publisher/reviewers/list', 'PublisherController@reviewerList')->name('/publisher/reviewers/list');
    Route::get('/publisher/reviewers/{id?}', 'PublisherController@reviewerShow');
    Route::match(['post', 'put'], '/publisher/reviewers/{id?}', 'PublisherController@reviewerSave');    
    Route::get('/publisher/reviewers/{id}/delete', 'PublisherController@reviewerDelete');
    
    Route::post('/publisher/reviewers/{reviewerId}/question/{id?}', 'PublisherController@saveQuestion');
    Route::match(['delete'], '/publisher/reviewers/{reviewerId}/question/{id?}', 'PublisherController@deleteQuestion');

    Route::post('/publisher/reviewers/{reviewerId}/learning-material/{id?}', 'PublisherController@saveLearningMaterial');
    Route::match(['delete'], '/publisher/reviewers/{reviewerId}/learning-material/{id?}', 'PublisherController@deleteLearningMaterial');
    
    Route::get('/publisher/reviewers/{reviewerId}/questionnaire-groups', 'PublisherController@questionnaireGroups' );
    Route::match(['post', 'put'], '/publisher/reviewers/{reviewerId}/questionnaire-group/{id?}', 'PublisherController@questionnaireGroupSave' );
    Route::match(['delete'], '/publisher/reviewers/{reviewerId}/questionnaire-group/{id}', 'PublisherController@questionnaireGroupDelete' );
    
    Route::livewire('/publisher/reviewer-list', 'publisher-reviewer-list');
    Route::view('/publisher/reviewer-list', 'publisher.reviewer-list');
    
    Route::post('/publisher/request-fund-withdrawal', 'PublisherController@requestFundWithdrawal');

    Route::view('/publisher/settings', 'publisher.settings');
    Route::view('/publisher/statement', 'publisher.statement');

    

});

Route::middleware(['auth'])->group(function()
{
    Route::get('/generateExam/{reviewerId}', 'ReviewerController@generateExam');
    Route::post('/saveExamResult', 'ReviewerController@saveExamResult');
    Route::get('/userExamSummary/{reviewerId}', 'ReviewerController@userExamSummary');

    Route::post('/tinymce/image-upload', 'HomeController@tinymceImageUpload');

    Route::get('/reviewer/{reviewrId}/learning-materials/{id?}', 'ReviewerController@viewLearningMaterials');
});

Route::middleware([])->group(function(){
    Route::get('/paymongo/buy-reviewer/{reviewerId}', 'PaymongoController@buyReviewer');
    Route::post('/paymongo/webhook', 'PaymongoController@webhook');
    Route::get('/paymongo/confirm-payment/{clientKey}', 'PaymongoController@confirmPayment');
});


