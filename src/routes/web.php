<?php
//Category
Route::get('/cms/toursdk/category/structure','budisteikul\toursdk\Controllers\CategoryController@structure')->middleware(['web','auth','verified','CoreMiddleware']);
Route::resource('/cms/toursdk/category','budisteikul\toursdk\Controllers\CategoryController',[ 'names' => 'route_toursdk_category' ])
	->middleware(['web','auth','verified','CoreMiddleware']);

//Product
Route::resource('/cms/toursdk/product','budisteikul\toursdk\Controllers\ProductController',[ 'names' => 'route_toursdk_product' ])
	->middleware(['web','auth','verified','CoreMiddleware']);
	
Route::resource('/cms/toursdk/channel','budisteikul\toursdk\Controllers\ChannelController',[ 'names' => 'route_toursdk_channel' ])
	->middleware(['web','auth','verified','CoreMiddleware']);