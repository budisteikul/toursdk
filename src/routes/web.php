<?php
//Category
Route::get('/cms/toursdk/category/structure','budisteikul\toursdk\Controllers\CategoryController@structure')->middleware(['web','auth','verified','CoreMiddleware']);
Route::resource('/cms/toursdk/category','budisteikul\toursdk\Controllers\CategoryController',[ 'names' => 'route_toursdk_category' ])
	->middleware(['web','auth','verified','CoreMiddleware']);

//Product
Route::resource('/cms/toursdk/product','budisteikul\toursdk\Controllers\ProductController',[ 'names' => 'route_toursdk_product' ])
	->middleware(['web','auth','verified','CoreMiddleware']);

//Channel	
Route::resource('/cms/toursdk/channel','budisteikul\toursdk\Controllers\ChannelController',[ 'names' => 'route_toursdk_channel' ])
	->middleware(['web','auth','verified','CoreMiddleware']);

//Booking
Route::get('/cms/toursdk/booking/calendar', 'budisteikul\toursdk\Controllers\BookingController@calendar')->middleware(['web','auth','verified','CoreMiddleware']);
Route::get('/cms/toursdk/booking/checkout', 'budisteikul\toursdk\Controllers\BookingController@checkout')->middleware(['web','auth','verified','CoreMiddleware']);
Route::resource('/cms/toursdk/booking','budisteikul\toursdk\Controllers\BookingController',[ 'names' => 'route_toursdk_booking' ])
	->middleware(['web','auth','verified','CoreMiddleware']);





Route::get('/snippets/activity/{activityId}/calendar/json/{year}/{month}', 'budisteikul\toursdk\Controllers\ShoppingCartController@snippetscalendar');
Route::get('/snippets/pdf/invoice/{id}', 'budisteikul\toursdk\Controllers\ShoppingCartController@invoice');
Route::get('/snippets/pdf/ticket/{id}', 'budisteikul\toursdk\Controllers\ShoppingCartController@ticket');
Route::post('/snippets/shoppingcart', 'budisteikul\toursdk\Controllers\ShoppingCartController@shoppingcart');
Route::post('/snippets/shoppingcart/checkout', 'budisteikul\toursdk\Controllers\ShoppingCartController@checkout');
Route::post('/snippets/promocode', 'budisteikul\toursdk\Controllers\ShoppingCartController@applypromocode');
Route::post('/snippets/promocode/remove', 'budisteikul\toursdk\Controllers\ShoppingCartController@removepromocode');
Route::post('/snippets/activity/remove', 'budisteikul\toursdk\Controllers\ShoppingCartController@removebookingid');
Route::post('/snippets/activity/invoice-preview', 'budisteikul\toursdk\Controllers\ShoppingCartController@snippetsinvoice');
Route::post('/snippets/widget/cart/session/{id}/activity', 'budisteikul\toursdk\Controllers\ShoppingCartController@addshoppingcart');



