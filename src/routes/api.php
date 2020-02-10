<?php
Route::group(['namespace' => 'Abs\ShippingMethodPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'shipping-method-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});