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

// MainController
Route::get('/', 'MainController@index');
Route::post('/', 'MainController@login');
Route::get('/logout', 'MainController@logout');

// Chair Controller
Route::get('/chair', 'ChairController@index');
Route::get('/chair/review/{id}', 'ChairController@review');
Route::post('/chair/review/{id}', 'ChairController@update');

// Volunteer Controller
Route::resource('volunteer', 'VolunteerController');

// Bazaar Controller
Route::resource('chair/bazaar', 'BazaarController');
Route::get('/chair/bazaar/curr/{id}', 'BazaarController@current');
Route::post('/chair/bazaar/{id}', 'BazaarController@update');
Route::get('/chair/bazaar/remove/{id}', 'BazaarController@removeVendor');

// Report Controller
Route::get('chair/reports', 'ReportController@index');
Route::post('chair/reports', 'ReportController@vendor');
Route::get('chair/reports/download', 'ReportController@rollupExcel');
Route::get('chair/reports/download/{id}', 'ReportController@vendorExcel');
Route::get('chair/reports/invoice/{id}', 'ReportController@invoice');

// Validation Controller
Route::get('salesSheet/validate/validate', 'ValidationController@validateSheet');
Route::post('salesSheet/validate/validate', 'ValidationController@store');
Route::get('salesSheet/validate/finalize', 'ValidationController@finalize');
Route::post('salesSheet/validate/finalize', 'ValidationController@save');
Route::get('salesSheet/validate/cancel', 'ValidationController@destroySession');
Route::get('salesSheet/validate/show/{id}', 'ValidationController@show');
Route::get('salesSheet/validate', 'ValidationController@select');
Route::post('salesSheet/validate', 'ValidationController@select');

// SalesSheetController
Route::get('salesSheet/info/create', 'SalesSheetController@createInfo');
Route::post('salesSheet/info/create', 'SalesSheetController@storeInfo');
Route::get('salesSheet/sales/create', 'SalesSheetController@createSales');
Route::post('salesSheet/sales/create', 'SalesSheetController@storeSales');
Route::get('salesSheet/finalize', 'SalesSheetController@finalize');
Route::post('salesSheet/finalize', 'SalesSheetController@storeSalesSheet');
Route::get('salesSheet/cancel', 'SalesSheetController@destroySession');
Route::resource('salesSheet', 'SalesSheetController', ['only' => ['index', 'show']]);

// HelperController
Route::get('help/vendorNames/name', 'HelperController@acVendorName');
Route::get('help/vendorNames', 'HelperController@acVendor');
Route::get('help/salesSheets', 'HelperController@acSalesSheet');
Route::get('help/search', ['middleware' => 'auth', 'uses' => 'HelperController@acSearch']);
Route::get('help/doSearch', ['middleware' => 'auth', 'uses' => 'HelperController@search']);

//Route::get('home', 'HomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	//'password' => 'Auth\PasswordController',
]);
