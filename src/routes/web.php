<?php
Route::get('/cms/toursdk/category/structure','budisteikul\toursdk\Controllers\CategoryController@structure')->middleware(['web','auth','verified','CoreMiddleware']);
Route::resource('/cms/toursdk/category','budisteikul\toursdk\Controllers\CategoryController',[ 'names' => 'route_toursdk_category' ])
	->middleware(['web','auth','verified','CoreMiddleware']);

