<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// import api for county scrap

Route::post('/import_scrap', 'BotController@importScrap')->name('bot.importScrap');

Route::any('/receivechat', 'ChatController@receivechat')->name('chat.receivechat');
Route::post('/fhinsure_log', 'FhinsureLogController@create')->name('fhinsure_log.create');
