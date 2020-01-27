<?php

Route::group(['namespace' => 'Abs\LocationPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'location-pkg'], function () {

	//COUNTRY
	Route::get('/countries/get-list', 'CountryController@getCountryList')->name('getCountryList');
	Route::get('/country/get-form-data', 'CountryController@getCountryFormData')->name('getCountryFormData');
	Route::post('/country/save', 'CountryController@saveCountry')->name('saveCountry');
	Route::get('/country/delete', 'CountryController@deleteCountry')->name('deleteCountry');
	Route::get('/country/view', 'CountryController@viewCountry')->name('viewCountry');

	//STATE
	Route::get('/states/get-list', 'StateController@getStateList')->name('getStateList');
	Route::get('/state/get-form-data/{id?}', 'StateController@getStateFormData')->name('getStateFormData');
	Route::post('/state/save', 'StateController@saveState')->name('saveState');
	Route::get('/state/delete/{id}', 'StateController@deleteState')->name('deleteState');

	//CITY
	Route::get('/cities/get-list', 'CityController@getCityList')->name('getCityList');
	Route::get('/city/get-form-data/{id?}', 'CityController@getCityFormData')->name('getCityFormData');
	Route::post('/city/save', 'CityController@saveCity')->name('saveCity');
	Route::get('/city/delete/{id}', 'CityController@deleteCity')->name('deleteCity');

	//REGION
	Route::get('/regions/get-list', 'RegionController@getRegionList')->name('getRegionList');
	Route::get('/region/get-form-data/{id?}', 'RegionController@getRegionFormData')->name('getRegionFormData');
	Route::post('/region/save', 'RegionController@saveRegion')->name('saveRegion');
	Route::get('/region/delete/{id}', 'RegionController@deleteRegion')->name('deleteRegion');

});