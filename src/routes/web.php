<?php

Route::group(['namespace' => 'Abs\LocationPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'location-pkg'], function () {

	//COUNTRY
	Route::get('/countries/get-list', 'CountryController@getCountryPkgList')->name('getCountryPkgList');
	Route::get('/country/get-form-data', 'CountryController@getCountryFormData')->name('getCountryFormData');
	Route::post('/country/save', 'CountryController@savePkgCountry')->name('savePkgCountry');
	Route::get('/country/delete', 'CountryController@deleteCountryPkg')->name('deleteCountryPkg');
	Route::get('/country/view', 'CountryController@viewCountryPkg')->name('viewCountryPkg');
	Route::post('/countries/get', 'CountryController@getCountries')->name('getCountries');

	//STATE
	Route::get('/states/get-list', 'StateController@getStateList')->name('getStateList');
	Route::get('/state/get-form-data', 'StateController@getStateFormData')->name('getStateFormData');
	Route::post('/state/save', 'StateController@saveState')->name('saveState');
	Route::get('/state/delete', 'StateController@deleteState')->name('deleteState');
	Route::get('/state/view', 'StateController@viewState')->name('viewState');
	Route::get('/state/state-filter', 'StateController@getStateFilter')->name('getStateFilter');
	Route::post('/state/get', 'StateController@getStates')->name('getStates');

	//CITY
	Route::get('/cities/get-list', 'CityController@getCityPkgList')->name('getCityPkgList');
	Route::get('/city/get-form-data', 'CityController@getCityFormData')->name('getCityFormData');
	Route::post('/city/save', 'CityController@saveCity')->name('saveCity');
	Route::get('/city/delete', 'CityController@deleteCityPkg')->name('deleteCityPkg');
	Route::get('/city/view', 'CityController@viewCityPkg')->name('viewCityPkg');
	Route::get('/city/city-filter', 'CityController@getCityFilter')->name('getCityFilter');

	//REGION
	Route::get('/regions/get-list', 'RegionController@getRegionList')->name('getRegionList');
	Route::get('/region/get-form-data', 'RegionController@getRegionFormData')->name('getRegionFormData');
	Route::post('/region/save', 'RegionController@saveRegion')->name('saveRegion');
	Route::get('/region/delete', 'RegionController@deleteRegion')->name('deleteRegion');
	Route::get('/region/view', 'RegionController@viewRegion')->name('viewRegion');
	Route::get('/region/get-filter', 'RegionController@getRegionFilter')->name('getRegionFilter');

	//GET STATE BASED COUNTRY
	Route::get('/states/get-state', 'StateController@getStateBasedCountry')->name('getStateBasedCountry');
	//GET CITY BASED STATE
	Route::get('/city/get-city', 'CityController@getCityBasedState')->name('getCityBasedState');

});