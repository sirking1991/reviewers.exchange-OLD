<?php

use Illuminate\Support\Facades\Route;

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

});

