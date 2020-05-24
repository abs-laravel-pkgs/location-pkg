<?php
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
