<?php
Route::group(['namespace' => 'Abs\LocationPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'location-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});