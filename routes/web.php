<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
Route::any("/testpurpose_url_test","Leads\LeadController@getcontactDetails");
Route::any("/testpurpose_url_sunbiz","Leads\LeadController@getSunbizDetails");
Route::get("/updateHistoricalLeadTotalPremium","Leads\LeadController@updateHistoricalLeadTotalPremium");

Route::post('/verifyOtp', 'Auth\LoginController@verifyOtp')->name('login.verifyOtp');
Route::post('/resendOtp', 'Auth\LoginController@resendOtp')->name('login.resendOtp');

Route::prefix('pipedrive')->group(function () {
    Route::get('/chat/{contactId}', 'pipedrive\PipedriveLoginController@contactChat');
    Route::post('/sendChat', 'pipedrive\PipedriveLoginController@sendChat');
    Route::post('/sendMail', 'pipedrive\PipedriveLoginController@sendMail');
    Route::post('/getTemplateData', 'pipedrive\PipedriveLoginController@getTemplateData');
    Route::post('/saveTemplate', 'pipedrive\PipedriveLoginController@saveTemplate');
    Route::post('/deleteTemplate', 'pipedrive\PipedriveLoginController@deleteTemplate');
    Route::get('/checkAlreadyLogin', 'pipedrive\PipedriveLoginController@checkAlreadyLogin');
    Route::get('/differentDealStatus', 'pipedrive\PipedriveLoginController@differentDealStatus');
    Route::get('/leadfiledownload/{id?}/{name?}', 'pipedrive\PipedriveLoginController@leadfiledownload');
    Route::get('/leadasanafiledownload/{id?}/{name?}', 'pipedrive\PipedriveLoginController@leadasanafiledownload');
    Route::post('/statusWiseLeadList', 'pipedrive\PipedriveLoginController@statusWiseLeadList');
    Route::post('/fetchTotalDealData', 'pipedrive\PipedriveLoginController@fetchTotalDealData');
    Route::post('/individualLeadData', 'pipedrive\PipedriveLoginController@individualLeadData');
    Route::post('/leadsNotesList', 'pipedrive\PipedriveLoginController@leadsNotesList');
    Route::post('/leadsLogsList', 'pipedrive\PipedriveLoginController@leadsLogsList');
    Route::post('/leadsFilesList', 'pipedrive\PipedriveLoginController@leadsFilesList');
    Route::post('/shiftLeadStatus', 'pipedrive\PipedriveLoginController@shiftLeadStatus');
    Route::post('/updateIndividualLead', 'pipedrive\PipedriveLoginController@updateIndividualLead');
    Route::post('/keepEventLog', 'pipedrive\PipedriveLoginController@keepEventLog');
    Route::post('/deleteEventLog', 'pipedrive\PipedriveLoginController@deleteEventLog');
    Route::post('/fetchLeadDataEmail', 'pipedrive\PipedriveLoginController@fetchLeadDataEmail');
    Route::post('/listSpecialStatus', 'pipedrive\PipedriveLoginController@listSpecialStatus');
    Route::post('/request_login', 'pipedrive\PipedriveLoginController@request_login');
    Route::post('/request_verify', 'pipedrive\PipedriveLoginController@request_verify');
    Route::post('/resendOtp', 'pipedrive\PipedriveLoginController@resendOtp');
    Route::post('/allStatusList', 'pipedrive\PipedriveLoginController@allStatusList');
    Route::post('/reassignLeadStatus', 'pipedrive\PipedriveLoginController@reassignLeadStatus');
    Route::post('/addLeadNote', 'pipedrive\PipedriveLoginController@addLeadNote');
    Route::post('/addLeadFile', 'pipedrive\PipedriveLoginController@addLeadFile');
    Route::post('/destroyLeadNote', 'pipedrive\PipedriveLoginController@destroyLeadNote');
    Route::post('/destroyLeadFile', 'pipedrive\PipedriveLoginController@destroyLeadFile');
    Route::post('/updateContact', 'pipedrive\PipedriveLoginController@updateContact');
    Route::post('/removeContact', 'pipedrive\PipedriveLoginController@removeContact');
    Route::post('/addContact', 'pipedrive\PipedriveLoginController@addContact');
    Route::get('/fetchRequiredContactInfo', 'pipedrive\PipedriveLoginController@fetchRequiredContactInfo');
    Route::post('/updateIndiLeadData', 'pipedrive\PipedriveLoginController@updateIndiLeadData');
    Route::post('/updateIndiLeadDataGroup', 'pipedrive\PipedriveLoginController@updateIndiLeadDataGroup');
    Route::post('/updateAdditionalPolicyLead', 'pipedrive\PipedriveLoginController@updateAdditionalPolicyLead');
    Route::post('/deleteAdditionalPolicyLead', 'pipedrive\PipedriveLoginController@deleteAdditionalPolicyLead');
    Route::get('/asanaStatusList', 'pipedrive\PipedriveLoginController@asanaStatusList');
    Route::post('/differentQuestionWiseLead', 'pipedrive\PipedriveLoginController@differentQuestionWiseLead');
    Route::post('/leadAsanaDetails', 'pipedrive\PipedriveLoginController@leadAsanaDetails');
    Route::post('/updateleadAsanaDetails', 'pipedrive\PipedriveLoginController@updateleadAsanaDetails');
    Route::post('/getCollaboratorDetails', 'pipedrive\PipedriveLoginController@getCollaboratorDetails');
    Route::post('/updateCollaboratorDetails', 'pipedrive\PipedriveLoginController@updateCollaboratorDetails');
    Route::post('/updateAssignee', 'pipedrive\PipedriveLoginController@updateAssignee');
    Route::get('/logout', 'pipedrive\PipedriveLoginController@logout');
    Route::get('/updateTestingDialingContact/{dialing_id}', 'pipedrive\PipedriveLoginController@updateTestingDialingContact');
    Route::get('/updated_dialing/{dialing_id}', 'pipedrive\PipedriveLoginController@updated_dialing');
    Route::get('/updateLeadStatusPipeline/{status}/{agent_id}', 'pipedrive\PipedriveLoginController@updateLeadStatusPipeline');
});

Route::prefix('agentreport')->middleware('check.report.auth')->group(function () {
	Route::get('/testUrlForBigOCean', 'ActivityReportController@testUrlForBigOCean');
	Route::get('/activity', 'ActivityReportController@activity')->name('agentreport.activityIndex');
    Route::get('/mailerleadtracker', 'ActivityReportController@mailerleadtracker')->name('agentreport.mailerLeadIndex');
    Route::get('/mailerleadtracker/edit/{id}', 'ActivityReportController@editMailTracker')->name('agentreport.editMailTracker');
    Route::post('/mailerleadtracker/delete/{id}', 'ActivityReportController@deleteMailTracker')->name('agentreport.deleteMailTracker');
    Route::get('/activityreport', 'ActivityReportController@activityReport')->name('agentreport.activityReport');
    Route::get('/mailerleadreport', 'ActivityReportController@mailerLeadReport')->name('agentreport.mailerLeadReport');
    Route::get('/dailycallreport', 'ActivityReportController@daillyCallReport')->name('agentreport.daillyCallReport');
    Route::post('/daily_call_report_list', 'ActivityReportController@dailycallReportList')->name('agentreport.dailycallReportList');
    Route::post('/daily_call_report_list_download', 'ActivityReportController@dailycallReportListDownload')->name('agentreport.dailycallReportListDownload');
    Route::post('/save_agent_activity', 'ActivityReportController@saveAgentActivity')->name('agentreport.saveAgentActivity');
    Route::post('/save_mail_lead_tracker', 'ActivityReportController@saveMailLeadTracker')->name('agentreport.saveMailLeadTracker');
    Route::post('/mail_lead_tracker_list', 'ActivityReportController@mailLeadTrackerList')->name('agentreport.mailLeadTrackerList');
    Route::post('/mail_lead_tracker_list_download', 'ActivityReportController@mailLeadTrackerListDownload')->name('agentreport.mailLeadTrackerListDownload');
    Route::post('/activity_list', 'ActivityReportController@activityList')->name('agentreport.activityList');
    Route::post('/activity_list_download', 'ActivityReportController@activityListDownload')->name('agentreport.activityListDownload');
    Route::get('/activity_details/{id}', 'ActivityReportController@activityDetails')->name('agentreport.activityDetails');
    Route::get('/file_download/{id}', 'ActivityReportController@fileDownload')->name('agentreport.file_download');
});

Route::get('/testurl', 'Leads\ContactController@testurl');
Route::get('/updateDialingLead', 'DialingController@updateDialingLead');
Route::get('/emails-to-klaviyo', 'SmtpConfigurationController@addEmailtoKlaviyo');
Route::get('/terms-condition', 'TermsPrivacyController@termsCondition')->name('terms-condition');
Route::get('/privacy-policy', 'TermsPrivacyController@privacyPolicy')->name('privacy-policy');
Route::any('/receivechat', 'ChatController@receivechat')->name('chat.receivechat');
Route::any('/check_max_execution_time/{contactId}', 'ChatController@check_max_execution_time')->name('chat.check_max_execution_time');
Route::get('/get-all-unread-msg', 'ChatController@getAllUnreadMsg')->name('getAllUnreadMsg');
Auth::routes(['register' => false]);



Route::get('/scrap', 'Leads\ScrapController@scrap')->name('scrap');
Route::get('/scrap_sunbiz', 'Leads\ScrapController@scrapSunbizGetLeads')->name('scrap_sunbiz');
Route::post('/scrap_sunbiz/delete', 'Leads\ScrapController@scrapSunbizdeleteLeads')->name('scrap_sunbiz.delete');
Route::any('/getDataBySunbizUrl', 'Leads\ScrapController@getDataBySunbizUrl')->name('getDataBySunbizUrl');
Route::any('/getScrapSunbizGetLeadsApi', 'Leads\ScrapController@getScrapSunbizGetLeadsApi')->name('getScrapSunbizGetLeadsApi');
Route::get('/scrap_county', 'Leads\ScrapController@scrap_county')->name('scrap_county');
Route::any('/import_scrap', 'BotController@import_scrap')->name('import_scrap');
Route::any('/scrap_white_pages', 'Leads\ScrapController@scrap_white_pages')->name('scrap_white_pages');
Route::any('/scrap_open_people_search/{firstname}/{lastname}/{state}', 'Leads\ScrapController@contactMailingAddressVerification')->name('scrap_open_people_search');
Route::any('/scrap_datalabs/{firstname}/{lastname}/{state}', 'Leads\ScrapController@contactFromPeopleDataLabs')->name('scrap_datalabs');
Route::post('/scrap/migratecontacts', 'Leads\ScrapController@migratecontacts')->name('migratecontacts');
Route::get('/scrap/compare/{id}', 'Leads\ScrapController@comparecontacts')->name('compare');
Route::any('/updateSingleBusinessName', 'Leads\ScrapController@updateSingleBusinessName')->name('updateSingleBusinessName');



//webhook Ricochet 
Route::get('/leads/getlead', 'Leads\LeadController@getLead')->name('leads.getLead');

Route::post('/fhinsure_log', 'FhinsureLogController@create')->name('fhinsure_log.create');

Route::group(['middleware' => ['auth']], function () { //for logged in users

	Route::get('/impersonate/{id}', 'ImpersonationController@impersonate')->name('impersonate');
	Route::get('/leave-impersonation', 'ImpersonationController@leaveImpersonation')->name('leave-impersonation');
	Route::post('/impersonate/search', 'ImpersonationController@search')->name('impersonate.search');
	Route::post('/community/search', 'ActivityReportController@searchComm')->name('community.searchComm');


	Route::get('doc', 'SettingController@doc')->name('settings.doc');
	Route::get('systemsetting', 'SettingController@systemsetting')->name('settings.systemsetting');
	Route::post('storesystemsetting', 'SettingController@storesystemsetting')->name('settings.storesystemsetting');
	Route::get('/permission/generate', 'SettingController@generate')->name('permission.generate');

	Route::get('/templates', 'TemplateController@index')->name('templates.index');
	Route::any('/template/listByUserid/alldata', 'TemplateController@listByUserId')->name('template.listByUserId');
	Route::post('/template/addNewTemplate/addNew', 'TemplateController@addNewTemplate')->name('template.addNewTemplate');
	Route::get('/template/deleteTemplate/delete/{templateId}', 'TemplateController@deleteTemplate')->name('template.deleteTemplate');
	Route::get('/template/singleDetail/contactDetail/{singleContactId}', 'TemplateController@contactDetail');
	Route::get('/template/singleDetail/templateDetail/{singleTemplateId}', 'TemplateController@templateDetail');

	Route::get('/', 'HomeController@index')->name('home');
	Route::get('/dashboard', 'HomeController@index')->name('dashboard');
	Route::get('/profile/{id}', 'HomeController@edit_profile')->name('profile');
	Route::post('/update/{id}', 'HomeController@update_profile')->name('update_profile');


	//import bot 
	Route::get('/bot-settings', 'BotController@botSettings')->name('bot.settings');
	Route::get('/bot-import', 'BotController@botImport')->name('bot.import');


	// Route::group(['middleware' => ['auth']], function () {
	Route::resource('roles', RoleController::class);
	Route::resource('users', UserController::class);
	Route::post('/users/update-2fa','UserController@update2FA')->name('users.update2fa');
	Route::get('/users/{id}/assign-team','UserController@assignTeam')->name('users.assignTeam');
	Route::post('/users/update-team','UserController@updateTeam')->name('users.updateTeam');

	//SMTP settings
	Route::get('/smtp-settings', 'SmtpConfigurationController@index')->name('smtp.settings');
	Route::get('/smtps', 'SmtpConfigurationController@adminIndex')->name('smtps.index');
	Route::post('/smtp-settings/store', 'SmtpConfigurationController@store')->name('smtp.store');
	//route for leade smtp datatables
	Route::post('/smtps/smtps-data', 'SmtpConfigurationController@get_smtps')->name('smtps.get_smtps');
	Route::post('/smtps/delete', 'SmtpConfigurationController@delete_smtps')->name('smtps.delete_smtps');
	Route::delete('/smtps/delete/{id}', 'SmtpConfigurationController@destroy')->name('smtps.destroy');
	Route::get('/smtps/show/{id}', 'SmtpConfigurationController@show')->name('smtps.show');
	Route::get('/smtps/create', 'SmtpConfigurationController@create')->name('smtps.create');
	Route::post('/smtps/create', 'SmtpConfigurationController@storeSmtp')->name('smtps.store');
	Route::get('/smtps/edit/{id}', 'SmtpConfigurationController@edit')->name('smtps.edit');
	Route::patch('/smtps/edit/{id}', 'SmtpConfigurationController@update')->name('smtps.update');
	Route::get('/provider-details/{id}', 'EmailProviderController@getProviderDetails')->name('provider.details');
	Route::get('/user-details/{id}', 'UserController@getAgentDetails')->name('user.details');
	Route::post('/contact/mail', 'Leads\ContactController@sendContactMail')->name('contact.mail');

	//business management
	Route::get('/leads', 'Leads\LeadController@index')->name('leads.index');
	Route::get('/leads/updateslug', 'Leads\LeadController@updateslug')->name('leads.updateslug');
	Route::get('/leads/updatecontactslug', 'Leads\LeadController@updatecontactslug')->name('leads.updatecontactslug');
	Route::get('/leads/show/{id}', 'Leads\LeadController@show')->name('leads.show');
	Route::get('/leads/merge/{id}', 'Leads\LeadController@merge')->name('leads.merge');
	Route::post('/leads/completemerge', 'Leads\LeadController@completemerge')->name('leads.completemerge');
	Route::post('/leads/moveContacts', 'Leads\LeadController@moveContacts')->name('leads.moveContacts');
	Route::get('/leads/create', 'Leads\LeadController@create')->name('leads.create');
	Route::post('/leads/create', 'Leads\LeadController@store')->name('leads.store');
	Route::get('/leads/edit/{id}', 'Leads\LeadController@edit')->name('leads.edit');
	Route::get('/leads/carrierList', 'Leads\LeadController@carrierList')->name('leads.carrierList');
	Route::patch('/leads/edit/{id}', 'Leads\LeadController@update')->name('leads.update');
	Route::delete('/leads/delete/{id}', 'Leads\LeadController@destroy')->name('leads.destroy');
	Route::post('/leads/filters', 'Leads\LeadController@filterStore')->name('leads.filterstore');
	Route::post('/leads/fetchDateWiseOlderData', 'Leads\LeadController@fetchDateWiseOlderData')->name('leads.fetchDateWiseOlderData');
	Route::post('/leads/filters-delete', 'Leads\LeadController@filterDelete')->name('leads.filterdelete');
	Route::get('/leads/autocomplete-search', 'Leads\LeadController@customSearch')->name('leads.customsearch');
	Route::get('/leads/location-leads-id', 'Leads\LeadController@getLeadsIdFromLocation')->name('leads.getLeadsIdFromLocation');
	Route::post('/leads/all-leads-location', 'Leads\LeadController@getAllLeadsLocation')->name('leads.getAllLeadsLocation');

	//current insurances on Lead
	Route::post('/leads/edit/new-current-insurance/{id}', 'Leads\LeadController@create_current_insurance')->name('leads.create_current_insurance');
	Route::post('/leads/edit/edit-current-insurance/{id}', 'Leads\LeadController@update_current_insurance')->name('leads.update_current_insurance');

	//route for datatables
	Route::post('/leads/leads-custom', 'Leads\LeadController@get_custom_leads')->name('leads.get_custom_leads');
	//leads actions
	Route::post('/leads/actions/{id}', 'Leads\ActionController@add_action')->name('leads.actions');
	Route::get('/actions/leads-contact-report', 'Leads\ActionController@index')->name('actions.index');
	//route for leade report datatables
	Route::post('/actions/datatable', 'Leads\ActionController@get_contact_report')->name('actions.get_contact_report');
	//route for leade template datatables
	Route::post('/templates/templates-data', 'TemplateController@get_templates')->name('templates.get_templates');
	Route::post('/templates/delete', 'TemplateController@delete_templates')->name('templates.delete_templates');
	Route::delete('/templates/delete/{id}', 'TemplateController@destroy')->name('templates.destroy');
	Route::get('/templates/show/{id}', 'TemplateController@show')->name('templates.show');
	Route::get('/templates/create', 'TemplateController@create')->name('templates.create');
	Route::post('/templates/create', 'TemplateController@store')->name('templates.store');
	Route::get('/templates/edit/{id}', 'TemplateController@edit')->name('templates.edit');
	Route::patch('/templates/edit/{id}', 'TemplateController@update')->name('templates.update');

	//route for leade template datatables
	Route::get('/unauthorised', 'SmsProviderController@unauthorised')->name('smsprovider.unauthorised');
	Route::get('/smsprovider', 'SmsProviderController@index')->name('smsprovider.index');
	Route::get('/smsproviderlist/{type?}/{id?}', 'SmsProviderController@listIndex')->name('smsprovider.listIndex');
	Route::post('/smsprovider/data', 'SmsProviderController@data')->name('smsprovider.data');
	Route::post('/smsproviderlist/data', 'SmsProviderController@listdata')->name('smsprovider.listdata');
	Route::post('/smsprovider/delete', 'SmsProviderController@delete_smsprovider')->name('smsprovider.delete_smsprovider');
	Route::delete('/smsprovider/delete/{id}', 'SmsProviderController@destroy')->name('smsprovider.destroy');
	Route::get('/smsprovider/show/{id}', 'SmsProviderController@show')->name('smsprovider.show');
	Route::get('/smsprovider/create', 'SmsProviderController@create')->name('smsprovider.create');
	Route::post('/smsprovider/create', 'SmsProviderController@store')->name('smsprovider.store');
	Route::get('/smsprovider/edit/{id}', 'SmsProviderController@edit')->name('smsprovider.edit');
	Route::patch('/smsprovider/edit/{id}', 'SmsProviderController@update')->name('smsprovider.update');

	Route::get('/contactstatus', 'ContactStatusController@index')->name('contactstatus.index');
	Route::post('/contactstatus/data', 'ContactStatusController@data')->name('contactstatus.data');
	Route::get('/contactstatus/create', 'ContactStatusController@create')->name('contactstatus.create');
	Route::post('/contactstatus/store', 'ContactStatusController@store')->name('contactstatus.store');
	Route::post('/contactstatus/update', 'ContactStatusController@update')->name('contactstatus.update');
	Route::get('/contactstatus/destroy/{id}', 'ContactStatusController@destroy')->name('contactstatus.destroy');
	Route::get('/contactstatus/edit/{id}', 'ContactStatusController@edit')->name('contactstatus.edit');
	Route::get('/contactstatus/show/{id}', 'ContactStatusController@show')->name('contactstatus.show');
	Route::post('/contactstatus/deletebulk', 'ContactStatusController@deletebulk')->name('contactstatus.deletebulk');

	Route::post('/carrier/data', 'CarrierController@data')->name('carrier.data');
	Route::post('/carrier/countLeadAssociation', 'CarrierController@countLeadAssociation')->name('carrier.countLeadAssociation');
	Route::post('/carrier/carrierFormSubmission', 'CarrierController@carrierFormSubmission')->name('carrier.carrierFormSubmission');
	Route::post('/carrier/forceDelete', 'CarrierController@forceDelete')->name('carrier.forceDelete');
	Route::get('/carrier/create/{type?}', 'CarrierController@create')->name('carrier.create');
	Route::post('/carrier/store', 'CarrierController@store')->name('carrier.store');
	Route::post('/carrier/update', 'CarrierController@update')->name('carrier.update');
	Route::get('/carrier/destroy/{id}', 'CarrierController@destroy')->name('carrier.destroy');
	Route::get('/carrier/show/{id}/{type?}', 'CarrierController@show')->name('carrier.show');
	Route::post('/carrier/deletebulk', 'CarrierController@deletebulk')->name('carrier.deletebulk');
	Route::get('/carrier/{type?}', 'CarrierController@index')->name('carrier.index');
	Route::get('/carrier/edit/{id}/{type?}', 'CarrierController@edit')->name('carrier.edit');

	Route::post('/rating/data', 'RatingController@data')->name('rating.data');
	Route::post('/rating/countLeadAssociationRating', 'RatingController@countLeadAssociationRating')->name('rating.countLeadAssociationRating');
	Route::post('/rating/ratingFormSubmission', 'RatingController@ratingFormSubmission')->name('rating.ratingFormSubmission');
	Route::post('/rating/forceDelete', 'RatingController@forceDelete')->name('rating.forceDelete');
	Route::get('/rating/create/{type?}', 'RatingController@create')->name('rating.create');
	Route::post('/rating/store', 'RatingController@store')->name('rating.store');
	Route::post('/rating/update', 'RatingController@update')->name('rating.update');
	Route::get('/rating/destroy/{id}', 'RatingController@destroy')->name('rating.destroy');
	Route::get('/rating/show/{id}/{type?}', 'RatingController@show')->name('rating.show');
	Route::post('/rating/deletebulk', 'RatingController@deletebulk')->name('rating.deletebulk');
	Route::get('/rating/{type?}', 'RatingController@index')->name('rating.index');
	Route::get('/rating/edit/{id}/{type?}', 'RatingController@edit')->name('rating.edit');

	Route::get('/leadsource', 'LeadSourceController@index')->name('leadsource.index');
	Route::post('/leadsource/data', 'LeadSourceController@data')->name('leadsource.data');
	Route::get('/leadsource/create/{type?}', 'LeadSourceController@create')->name('leadsource.create');
	Route::get('/leadsource/edit/{id}', 'LeadSourceController@edit')->name('leadsource.edit');
	Route::get('/leadsource/show/{id}', 'LeadSourceController@show')->name('leadsource.show');
	Route::post('/leadsource/store', 'LeadSourceController@store')->name('leadsource.store');
	Route::post('/leadsource/update', 'LeadSourceController@update')->name('leadsource.update');
	Route::get('/leadsource/destroy/{id}', 'LeadSourceController@destroy')->name('leadsource.destroy');
	Route::post('/leadsource/deletebulk', 'LeadSourceController@deletebulk')->name('leadsource.deletebulk');



	//route to export
	Route::get('/leads/export', 'Leads\ImportController@exportCsv')->name('leads.export');
	// route to import page
	Route::get('/leads/import', 'Leads\ImportController@import_leads')->name('leads.import');
	Route::post('/leads/import', 'Leads\ImportController@import')->name('leads.imports');

	Route::get('/leads/business', 'Leads\ImportController@import_businesses')->name('leads.business');
	Route::post('/leads/business/upload', 'Leads\ImportController@process_business')->name('leads.processBusiness');

	// Route::post('/leads/businesses', 'Leads\ImportController@update_businesses')->name('leads.businesses');
	// route to import contracts update csv
	Route::get('/leads/contacts', 'Leads\ImportController@import_contacts')->name('leads.importContacts');
	Route::post('/leads/contacts/upload', 'Leads\ImportController@process_contacts')->name('leads.processContacts');
	//save as campaign
	Route::POST('/leads/save-campaign', 'Leads\LeadController@save_campaign')->name('leads.save_campaign');
	//bulk remove leads
	Route::get('/leads/remove-leads', 'Leads\LeadController@remove_leads')->name('leads.remove_leads');
	Route::post('/leads/delete-leads', 'Leads\LeadController@delete_leads')->name('leads.delete_leads');

	//contacts lead
	Route::post('/leads/edit/contact-update/{id}', 'Leads\ContactController@contact_update')->name('leads.contact_update');
	Route::post('/leads/edit/contact-new/{id}', 'Leads\ContactController@contact_store')->name('leads.contact_store');
	Route::post('/leads/edit/contact-status-update/{id}', 'Leads\ContactController@contact_status_update')->name('leads.contact_status_update');
	Route::delete('/leads/edit/contact-delete/{id}', 'Leads\ContactController@contact_destroy')->name('leads.contact_destroy');

	Route::get('/registeragent', 'RegisterAgentController@index')->name('registeragent.index');
	Route::get('/registeragent/data', 'RegisterAgentController@data')->name('registeragent.data');

	//contacts all
	Route::get('/contacts', 'Leads\ContactController@index')->name('contacts.index');
	Route::get('/contacts/remove', 'Leads\ContactController@remove_contacts')->name('contacts.remove_contacts');
	Route::post('/contacts/delete', 'Leads\ContactController@delete_contacts')->name('contacts.delete_contacts');
	Route::post('/contacts/mark_comolete_chat', 'Leads\ContactController@mark_comolete_chat')->name('contacts.mark_comolete_chat');
	Route::post('/contacts/mark_stop_chat', 'Leads\ContactController@mark_stop_chat')->name('contacts.mark_stop_chat');
	Route::post('/contacts/updateContactStatus', 'Leads\ContactController@updateContactStatus')->name('contacts.updateContactStatus');
	//route for datatables
	Route::get('/contacts/data', 'Leads\ContactController@data')->name('contacts.data');
	Route::get('/contacts/merge/{id}', 'Leads\ContactController@merge')->name('contacts.merge');
	Route::post('/contacts/completemerge', 'Leads\ContactController@completemerge')->name('contacts.completemerge');

	//notes
	Route::get('/leads/show/note-show-modal/{id}', 'Leads\NoteController@note_show_modal')->name('note_show_modal');
	Route::post('/leads/show/note-update/{id}', 'Leads\NoteController@note_update')->name('leads.note_update');
	Route::post('/leads/edit/note-new/{id}', 'Leads\NoteController@note_store')->name('leads.note_store');
	Route::delete('/leads/edit/note-delete/{id}', 'Leads\NoteController@note_destroy')->name('leads.note_destroy');

	//file upload
	Route::post('/leads/upload-file/{id}', 'FileUploadController@fileUpload')->name('leads.fileUpload');
	Route::get('/leads/edit/file-download/{id}', 'FileUploadController@file_download')->name('leads.file_download');
	Route::delete('/leads/edit/file-delete/{id}', 'FileUploadController@file_destroy_onlead')->name('files.file_destroy_onlead');
	//route for file datatables
	Route::post('/leads/leads-files', 'FileUploadController@files_table')->name('leads.files_table');


	//marketing campaigns
	Route::get('/marketing-campaigns', 'CampaignController@index')->name('campaigns.index');
	Route::post('/marketing-campaigns-table', 'CampaignController@campaigns_table')->name('campaigns.campaigns_table');
	Route::get('/marketing-campaigns/show/{id}', 'CampaignController@show')->name('campaigns.show');
	Route::get('/marketing-campaigns/edit/{id}', 'CampaignController@edit')->name('campaigns.edit');
	Route::patch('/marketing-campaigns/edit/{id}', 'CampaignController@update')->name('campaigns.update');
	Route::delete('/marketing-campaigns/delete/{id}', 'CampaignController@destroy')->name('campaigns.destroy');
	Route::get('/marketing-campaigns/files/delete', 'FileUploadController@file_destroy_oncampaign')->name('files.file_destroy_oncampaign');
	Route::post('/marketing-campaigns/files/upload', 'FileUploadController@upload_file_oncampaign')->name('files.upload_file_oncampaign');
	// file showing in browser
	Route::get('/marketing-campaigns/files/{id}/{filename}', 'FileUploadController@retrieve_files_fromStorage')->name('file.retrieve_files_fromStorage');

	// Agent
	Route::get('/dialings', 'DialingController@index')->name('dialings.index');
	Route::get('/dialings/dialings-custom', 'DialingController@dialingListApi')->name('dialings.dialingListApi');
	Route::post('/dialings/create', 'DialingController@create')->name('dialings.create');
	Route::post('/dialings/update-owner/{leadId}', 'DialingController@updateOwner')->name('dialings.updateOwner');
	Route::post('/dialings/assign', 'DialingController@assign')->name('dialings.assign');
	Route::post('/dialings/reassignagent', 'DialingController@reassignagent')->name('dialings.reassignagent');
	Route::post('/dialings/statuschange', 'DialingController@statuschange')->name('dialings.statuschange');
	Route::post('/agentleads/statuschange', 'DialingController@agentleadsstatuschange')->name('dialings.agentleads');
	Route::post('/dialings/assignLeads', 'DialingController@assignLeads')->name('dialings.assignLeads');
	Route::post('/dialings/assignagentlead', 'DialingController@assignagentlead')->name('dialings.assignagentlead');

	Route::post('/dialings/listdetails', 'DialingController@listdetails')->name('dialings.listdetails');
	Route::get('/dialings/show/{id}', 'DialingController@show')->name('dialings.show');
	Route::get('/dialings/dialings-leads-custom', 'DialingController@dialingDetailsApi')->name('dialings.dialingDetailsApi');
	Route::post('/dialings/ownLeads', 'DialingController@ownLeads')->name('dialings.ownLeads');
	Route::delete('/dialings/delete/{id}', 'DialingController@destroy')->name('dialings.destroy');

	Route::get('/dialings/ownedleads', 'DialingController@ownedleads')->name('dialings.ownedleads');
	Route::get('/dialings/ownedleadsdaywise', 'DialingController@ownedleadsdaywise')->name('dialings.ownedleadsdaywise');
	Route::get('/dialings/dialingsOwnedLeads', 'DialingController@dialingsOwnedLeads')->name('dialings.dialingsOwnedLeads');
	Route::get('/dialings/dialingsOwnedLeads_daywise', 'DialingController@dialingsOwnedLeads_daywise')->name('dialings.dialingsOwnedLeads_daywise');
	Route::post('/dialings/dialingsOwnedLeads_daywise_export', 'DialingController@dialingsOwnedLeads_daywise_export')->name('dialings.dialingsOwnedLeads_daywise_export');
	Route::post('/dialings/updatecontactleads', 'DialingController@updatecontactleads')->name('dialings.updatecontactleads');

	Route::get('/agents/reports', 'AgentController@reports')->name('agents.reports');
	Route::get('/agents/getReportsDataApi', 'AgentController@getReportsDataApi')->name('dialings.getReportsDataApi');


	Route::post('/dialings/callinitiated', 'DialingController@callinitiated')->name('dialings.callinitiated');
	Route::get('/dialings/module/{id}', 'DialingController@module')->name('dialings.module');


	Route::get('/chat/{contactId}/{newsletter_type?}', 'ChatController@index')->name('chat.index');
	Route::post('/chat', 'ChatController@store')->name('chat.store');
	// Route::post('/chat', 'ChatController@chatsms')->name('chat.chatsms');
	// Route::post('/receivechat', 'ChatController@receivechat')->name('chat.receivechat');

	/*
	* All Platforms Apis -- start
	*/
	//Index listing page view
	Route::get('/platform_setting/index', 'Leads\ScrapApiPlatformController@index')->name('platform_setting.index');
	//Listing api for index page
	Route::get('/platform_setting/getApiPlatforms', 'Leads\ScrapApiPlatformController@getApiPlatforms')->name('platform_setting.getApiPlatforms');
	//Create View
	Route::get('/platform_setting/create', 'Leads\ScrapApiPlatformController@create')->name('platform_setting.create');
	//create api
	Route::post('/platform_setting/store', 'Leads\ScrapApiPlatformController@store')->name('platform_setting.store');
	//Edit View
	Route::get('/platform_setting/edit/{id}', 'Leads\ScrapApiPlatformController@edit')->name('platform_setting.edit');
	//Update api
	Route::post('/platform_setting/update/{id}', 'Leads\ScrapApiPlatformController@update')->name('platform_setting.update');
	//Delete api
	Route::delete('/platform_setting/delete/{id}', 'Leads\ScrapApiPlatformController@delete')->name('platform_setting.delete');


	/**
	 * Scrap Contact
	 * **/
	//View
	Route::get('/platform_setting/scrap_contact_view/', 'Leads\ScrapContactController@scrapContactView')->name('platform_setting.scrap_contact_view');
	//Scrap submit api
	Route::post('/platform_setting/scrap_contact/', 'Leads\ScrapContactController@callForScrapContact')->name('platform_setting.scrap_contact');

	Route::get('/platform_setting/scrap_export/{status}', 'Leads\ScrapContactController@exportCsv')->name('platform_setting.scrap_export');
	/*
	* All Platforms Apis -- End
	*/

	// fhinsure data log
	Route::get('/newsletter/{id?}', 'FhinsureLogController@index')->name('newsletter.index');
	Route::post('/newsletter/get_fhinsure_log', 'FhinsureLogController@get_fhinsure_log')->name('newsletter.get_fhinsure_log');
	Route::post('/newsletter/delete', 'FhinsureLogController@delete_logs')->name('newsletter.delete_logs');
	Route::delete('/newsletter/delete/{id}', 'FhinsureLogController@destroy')->name('newsletter.destroy');
	Route::get('/newsletter/show/{id}', 'FhinsureLogController@show')->name('newsletter.show');
	Route::get('/newsletter/singleDetail/logDetail/{singleContactId}', 'FhinsureLogController@contactDetail');
	Route::post('/newsletter/message', 'FhinsureLogController@send_message')->name('newsletter.send_message');

	Route::match(['get', 'post'], '/{moduleName}/{methodName?}/{action?}/{id?}', function ($moduleName, Request $request) {
		$controllerClass = '\\App\\Http\\Controllers\\' . ucfirst($moduleName) . 'Controller';
		$modelClass = '\\App\\Model\\' . ucfirst($moduleName);

		// Check if the controller class exists
		if (!class_exists($controllerClass) || !class_exists($modelClass)) {
			abort(404, "Controller or Model not found for module: $moduleName");
		}

		$args = func_get_args();
		// dd($args);
		$methodName = isset($args[2]) ? $args[2] : 'list';
		$apimethodName = isset($args[3]) ? $args[3] : 'list';
		$viewEditId = ($apimethodName > 0) ? $apimethodName : 0;


		$invokeMethodName = $methodName;
		$request->viewEditId = $viewEditId;
		if ($methodName == 'list' || $apimethodName == 'list') {
			$request->apiCall = isset($args[3]) ? 1 : 0;
			$invokeMethodName = 'list';
		}
		if ($apimethodName == 'getViewApiData') {
			$request->apiCall = 1;
			$invokeMethodName = 'view';
		}
		if ($apimethodName == 'getEditApiData') {
			$request->apiCall = 1;
			$request->viewEditId = $args[3];
			$invokeMethodName = 'edit';
		}

		// Check if the method exists in the controller class
		if (!method_exists($controllerClass, $invokeMethodName)) {
			abort(404);
		}

		$request->moduleName = $moduleName;


		return app()->make($controllerClass)->{$invokeMethodName}($request);
	})->name('{moduleName}.list') // Dynamically generate route name
		->where('moduleName', '[a-zA-Z0-9-_]+');
});




// Auth::routes();