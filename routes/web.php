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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['checkifadmin'])->group(function () {
    Route::get('/admin/reviewers/list', 'ReviewerController@adminList')->name('/admin/reviewers/list');
    Route::get('/admin/reviewers/{id?}', 'ReviewerController@adminShow');
    Route::match(['post', 'put'], '/admin/reviewers/{id?}', 'ReviewerController@save');    
    Route::get('/admin/reviewers/{id}/delete', 'ReviewerController@delete');
    
    Route::match(['post', 'put'], '/admin/reviewers/{reviewerId}/question/{id?}', 'ReviewerController@saveQuestion');
    Route::match(['delete'], '/admin/reviewers/{reviewerId}/question/{id?}', 'ReviewerController@deleteQuestion');
    
    Route::get('/admin/reviewers/{reviewerId}/questionnaire-groups', 'ReviewerController@questionnaireGroups' );
    Route::match(['post', 'put'], '/admin/reviewers/{reviewerId}/questionnaire-group/{id?}', 'ReviewerController@questionnaireGroupSave' );
    Route::match(['delete'], '/admin/reviewers/{reviewerId}/questionnaire-group/{id}', 'ReviewerController@questionnaireGroupDelete' );
    
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
    Route::match(['post', 'put', 'get'], '/paymaya/redirectUrl/{status}/{reference}', 'PaymayaController@redirect');
});


