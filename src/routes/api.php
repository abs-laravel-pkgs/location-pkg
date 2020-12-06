<?php
use App\Http\Controllers\Api\Masters\Locations\StateApiController;
use App\Http\Controllers\Api\Masters\Locations\CityApiController;

Route::group(['middleware' => ['api']], function () {
	Route::group(['prefix' => '/api/masters/locations/state'], function () {
		$className = StateApiController::class;
		Route::get('index', $className . '@index');
		Route::get('read/{id}', $className . '@read');
		Route::post('save', $className . '@save');
		Route::get('options', $className . '@options');
		Route::get('delete/{role}', $className . '@delete');
	});

	Route::group(['prefix' => '/api/masters/locations/city'], function () {
		$className = CityApiController::class;
		Route::get('index', $className . '@index');
		Route::get('read/{id}', $className . '@read');
		Route::post('save', $className . '@save');
		Route::get('options', $className . '@options');
		Route::get('delete/{permission}', $className . '@delete');
	});
});

Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware' => ['auth:api']], function () {
	Route::group(['prefix' => 'api'], function () {
		//STATE
		Route::post('state/get-drop-down-List', 'StateController@getDropDownList');

		//CITY
		Route::post('city/get-drop-down-List', 'CityController@getDropDownList');

		//REGION
		Route::post('region/get-drop-down-List', 'RegionController@getDropDownList');

	});
});
