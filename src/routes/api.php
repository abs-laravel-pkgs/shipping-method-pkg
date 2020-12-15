<?php

Route::group(['middleware' => ['api','auth:api']], function () {

	Route::group(['prefix' => '/api/shipping-method'], function () {
		$className = \App\Http\Controllers\Api\Masters\ShippingMethodApiController::class;
		Route::get('index', $className . '@index');
		Route::get('read/{id}', $className . '@read');
		Route::post('save', $className . '@save');
		Route::get('options', $className . '@options');
		Route::get('delete/{id}', $className . '@delete');
	});
});
