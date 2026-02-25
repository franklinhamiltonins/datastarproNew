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
Route::middleware(['web_no_csrf'])
->prefix('pipedrive')
->group(function () {
    Route::post('/request_login', 'pipedrive\PipedriveLoginController@request_login');
    Route::post('/request_verify', 'pipedrive\PipedriveLoginController@request_verify');
    Route::post('/resendOtp', 'pipedrive\PipedriveLoginController@resendOtp');
});
Route::middleware(['web_no_csrf', 'auth:web'])->prefix('pipedrive')->group(function () {
    Route::get('/chat/{contactId}', 'pipedrive\PipedriveTemplateController@contactChat');
    Route::post('/sendChat', 'pipedrive\PipedriveTemplateController@sendChat');
    Route::post('/sendMail', 'pipedrive\PipedriveTemplateController@sendMail');
    Route::post('/getTemplateData', 'pipedrive\PipedriveTemplateController@getTemplateData');
    Route::post('/saveTemplate', 'pipedrive\PipedriveTemplateController@saveTemplate');
    Route::post('/deleteTemplate', 'pipedrive\PipedriveTemplateController@deleteTemplate');

    Route::get('/checkAlreadyLogin', 'pipedrive\PipedriveLoginController@checkAlreadyLogin');
    Route::get('/logout', 'pipedrive\PipedriveLoginController@logout');

    Route::get('/differentDealStatus', 'pipedrive\PipedriveInitialLoadController@differentDealStatus');
    Route::post('/statusWiseLeadList', 'pipedrive\PipedriveInitialLoadController@statusWiseLeadList');
    Route::post('/fetchTotalDealData', 'pipedrive\PipedriveInitialLoadController@fetchTotalDealData');
    Route::get('/asanaStatusList', 'pipedrive\PipedriveInitialLoadController@asanaStatusList');
    Route::post('/differentQuestionWiseLead', 'pipedrive\PipedriveInitialLoadController@differentQuestionWiseLead');
    Route::post('/allStatusList', 'pipedrive\PipedriveInitialLoadController@allStatusList');
    Route::post('/listSpecialStatus', 'pipedrive\PipedriveInitialLoadController@listSpecialStatus');
    Route::post('/leadsNotesList', 'pipedrive\PipedriveInitialLoadController@leadsNotesList');
    Route::post('/leadsLogsList', 'pipedrive\PipedriveInitialLoadController@leadsLogsList');
    Route::post('/leadsFilesList', 'pipedrive\PipedriveInitialLoadController@leadsFilesList');

    Route::get('/leadfiledownload/{id?}/{name?}', 'pipedrive\PipedriveLeadController@leadfiledownload');
    Route::get('/leadasanafiledownload/{id?}/{name?}', 'pipedrive\PipedriveLeadController@leadasanafiledownload');
    Route::post('/individualLeadData', 'pipedrive\PipedriveLeadController@individualLeadData');
    Route::post('/shiftLeadStatus', 'pipedrive\PipedriveLeadController@shiftLeadStatus');
    Route::post('/updateIndividualLead', 'pipedrive\PipedriveLeadController@updateIndividualLead');
    Route::post('/fetchLeadDataEmail', 'pipedrive\PipedriveLeadController@fetchLeadDataEmail');
    Route::post('/reassignLeadStatus', 'pipedrive\PipedriveLeadController@reassignLeadStatus');
    Route::post('/addLeadNote', 'pipedrive\PipedriveLeadController@addLeadNote');
    Route::post('/addLeadFile', 'pipedrive\PipedriveLeadController@addLeadFile');
    Route::post('/destroyLeadNote', 'pipedrive\PipedriveLeadController@destroyLeadNote');
    Route::post('/destroyLeadFile', 'pipedrive\PipedriveLeadController@destroyLeadFile');
    Route::post('/updateIndiLeadData', 'pipedrive\PipedriveLeadController@updateIndiLeadData');
    Route::post('/updateIndiLeadDataGroup', 'pipedrive\PipedriveLeadController@updateIndiLeadDataGroup');
    Route::post('/updateAdditionalPolicyLead', 'pipedrive\PipedriveLeadController@updateAdditionalPolicyLead');
    Route::post('/deleteAdditionalPolicyLead', 'pipedrive\PipedriveLeadController@deleteAdditionalPolicyLead');
    Route::post('/leadAsanaDetails', 'pipedrive\PipedriveLeadController@leadAsanaDetails');
    Route::post('/updateleadAsanaDetails', 'pipedrive\PipedriveLeadController@updateleadAsanaDetails');
    Route::post('/getCollaboratorDetails', 'pipedrive\PipedriveLeadController@getCollaboratorDetails');
    Route::post('/updateCollaboratorDetails', 'pipedrive\PipedriveLeadController@updateCollaboratorDetails');
    Route::post('/updateAssignee', 'pipedrive\PipedriveLeadController@updateAssignee');

    Route::post('/updateContact', 'pipedrive\PipedriveContactController@updateContact');
    Route::post('/removeContact', 'pipedrive\PipedriveContactController@removeContact');
    Route::post('/addContact', 'pipedrive\PipedriveContactController@addContact');
    Route::get('/fetchRequiredContactInfo', 'pipedrive\PipedriveContactController@fetchRequiredContactInfo');
    Route::post('/keepEventLog', 'pipedrive\PipedriveContactController@keepEventLog');
    Route::post('/deleteEventLog', 'pipedrive\PipedriveContactController@deleteEventLog');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// import api for county scrap

Route::post('/import_scrap', 'BotController@importScrap')->name('bot.importScrap');

Route::any('/receivechat', 'ChatController@receivechat')->name('chat.receivechat');
Route::post('/fhinsure_log', 'FhinsureLogController@create')->name('fhinsure_log.create');
