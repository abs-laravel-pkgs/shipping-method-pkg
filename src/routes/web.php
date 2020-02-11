<?php

Route::group(['namespace' => 'Abs\ShippingMethodPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'shipping-method-pkg'], function () {
	//FAQs
	Route::get('/shipping-methods/get-list', 'ShippingMethodController@getShippingMethodList')->name('getShippingMethodList');
	Route::get('/shipping-method/get-form-data', 'ShippingMethodController@getShippingMethodFormData')->name('getShippingMethodFormData');
	Route::post('/shipping-method/save', 'ShippingMethodController@saveShippingMethod')->name('saveShippingMethod');
	Route::get('/shipping-method/delete', 'ShippingMethodController@deleteShippingMethod')->name('deleteShippingMethod');
});

Route::group(['namespace' => 'Abs\ShippingMethodPkg', 'middleware' => ['web'], 'prefix' => 'shipping-method-pkg'], function () {
	//FAQs
	Route::get('/shipping-methods/get', 'ShippingMethodController@getShippingMethods')->name('getShippingMethods');
});
