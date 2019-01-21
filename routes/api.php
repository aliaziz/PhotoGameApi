<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/like', [
        'uses'=>'MobileAPI@likePhoto'
    ]);

    Route::post('/dislike', [
        'uses'=>'MobileAPI@dislikePhoto'
    ]);

    Route::post('/view', [
        'uses'=>'MobileAPI@viewPhoto'
    ]);

    Route::get('/photos', [
        'uses'=>'MobileAPI@allPhotos'
    ]);

    Route::get('/leadership-board', [
        'uses'=>'MobileAPI@getLeadershipBoard'
    ]);

    Route::post('/upload-photo', [
        'uses'=>'MobileAPI@uploadPhoto'
    ]);
});


Route::post('/login', [
    'uses'=>'MobileAPI@login'
]);

Route::post('/register', [
    'uses'=>'MobileAPI@register'
]);
