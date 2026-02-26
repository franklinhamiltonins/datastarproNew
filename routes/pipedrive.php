<?php
namespace App\Http\Controllers\pipedrive;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pipedrive Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "web_no_csrf" middleware group. Enjoy building your API!
|
*/

Route::post('/request_login',      [PipedriveLoginController::class, 'request_login']);
Route::post('/request_verify',     [PipedriveLoginController::class, 'request_verify']);
Route::post('/resendOtp',          [PipedriveLoginController::class, 'resendOtp']);

// --- Chat / Template ---
Route::get('/chat/{contactId}',                    [PipedriveTemplateController::class, 'contactChat']);
Route::post('/sendChat',                           [PipedriveTemplateController::class, 'sendChat']);
Route::post('/sendMail',                           [PipedriveTemplateController::class, 'sendMail']);
Route::post('/getTemplateData',                    [PipedriveTemplateController::class, 'getTemplateData']);
Route::post('/saveTemplate',                       [PipedriveTemplateController::class, 'saveTemplate']);
Route::post('/deleteTemplate',                     [PipedriveTemplateController::class, 'deleteTemplate']);

// --- Login Check ---
Route::get('/checkAlreadyLogin',                   [PipedriveLoginController::class, 'checkAlreadyLogin']);
Route::get('/logout',                              [PipedriveLoginController::class, 'logout']);

// --- Initial Load ---
Route::get('/differentDealStatus',                 [PipedriveInitialLoadController::class, 'differentDealStatus']);
Route::post('/statusWiseLeadList',                 [PipedriveInitialLoadController::class, 'statusWiseLeadList']);
Route::post('/fetchTotalDealData',                 [PipedriveInitialLoadController::class, 'fetchTotalDealData']);
Route::get('/asanaStatusList',                     [PipedriveInitialLoadController::class, 'asanaStatusList']);
Route::post('/differentQuestionWiseLead',          [PipedriveInitialLoadController::class, 'differentQuestionWiseLead']);
Route::post('/allStatusList',                      [PipedriveInitialLoadController::class, 'allStatusList']);
Route::post('/listSpecialStatus',                  [PipedriveInitialLoadController::class, 'listSpecialStatus']);
Route::post('/leadsNotesList',                     [PipedriveInitialLoadController::class, 'leadsNotesList']);
Route::post('/leadsLogsList',                      [PipedriveInitialLoadController::class, 'leadsLogsList']);
Route::post('/leadsFilesList',                     [PipedriveInitialLoadController::class, 'leadsFilesList']);

// --- Lead Files ---
Route::get('/leadfiledownload/{id?}/{name?}',      [PipedriveLeadController::class, 'leadfiledownload']);
Route::get('/leadasanafiledownload/{id?}/{name?}', [PipedriveLeadController::class, 'leadasanafiledownload']);

// --- Individual Lead ---
Route::post('/individualLeadData',                 [PipedriveLeadController::class, 'individualLeadData']);
Route::post('/shiftLeadStatus',                    [PipedriveLeadController::class, 'shiftLeadStatus']);
Route::post('/updateIndividualLead',               [PipedriveLeadController::class, 'updateIndividualLead']);
Route::post('/fetchLeadDataEmail',                 [PipedriveLeadController::class, 'fetchLeadDataEmail']);
Route::post('/reassignLeadStatus',                 [PipedriveLeadController::class, 'reassignLeadStatus']);
Route::post('/addLeadNote',                        [PipedriveLeadController::class, 'addLeadNote']);
Route::post('/addLeadFile',                        [PipedriveLeadController::class, 'addLeadFile']);
Route::post('/destroyLeadNote',                    [PipedriveLeadController::class, 'destroyLeadNote']);
Route::post('/destroyLeadFile',                    [PipedriveLeadController::class, 'destroyLeadFile']);
Route::post('/updateIndiLeadData',                 [PipedriveLeadController::class, 'updateIndiLeadData']);
Route::post('/updateIndiLeadDataGroup',            [PipedriveLeadController::class, 'updateIndiLeadDataGroup']);
Route::post('/updateAdditionalPolicyLead',         [PipedriveLeadController::class, 'updateAdditionalPolicyLead']);
Route::post('/deleteAdditionalPolicyLead',         [PipedriveLeadController::class, 'deleteAdditionalPolicyLead']);
Route::post('/leadAsanaDetails',                   [PipedriveLeadController::class, 'leadAsanaDetails']);
Route::post('/updateleadAsanaDetails',             [PipedriveLeadController::class, 'updateleadAsanaDetails']);
Route::post('/getCollaboratorDetails',             [PipedriveLeadController::class, 'getCollaboratorDetails']);
Route::post('/updateCollaboratorDetails',          [PipedriveLeadController::class, 'updateCollaboratorDetails']);
Route::post('/updateAssignee',                     [PipedriveLeadController::class, 'updateAssignee']);

// --- Contact ---
Route::post('/updateContact',                      [PipedriveContactController::class, 'updateContact']);
Route::post('/removeContact',                      [PipedriveContactController::class, 'removeContact']);
Route::post('/addContact',                         [PipedriveContactController::class, 'addContact']);
Route::get('/fetchRequiredContactInfo',            [PipedriveContactController::class, 'fetchRequiredContactInfo']);
Route::post('/keepEventLog',                       [PipedriveContactController::class, 'keepEventLog']);
Route::post('/deleteEventLog',                     [PipedriveContactController::class, 'deleteEventLog']);