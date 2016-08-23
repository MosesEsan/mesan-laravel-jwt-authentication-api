<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix' => 'api'], function() {

    //anything goes here will be under api/
    Route::post('login', 'Api\AuthController@login');
    Route::post('register', 'Api\AuthController@register');
    Route::get('verify/{type}/{confirmationCode}', 'Api\AuthController@verifyUser');
    Route::post('resend', 'Api\AuthController@resendVerification');
    Route::post('recover', 'Api\AuthController@recoverPassword');

//    Route::get('send', 'Api\AuthController@sendSMSVerification');


    Route::group(['middleware' => ['jwt.auth']], function() {
        Route::post('logout', 'Api\AuthController@logout');

        Route::get('test', function(){
            return response()->json(['foo'=>'bar']);
        });

        Route::get('subscribe-list', function(){
            return response()->json(['1'=>'Technology','2'=>'Music',
                '3'=>'Education','4'=>'Startup','5'=>'Events',
                '6'=>'Photography','7'=>'Drama','8'=>'Entertainment', ]);
        });
    });
});
