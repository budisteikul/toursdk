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

//Review
Route::resource('/cms/toursdk/review','budisteikul\toursdk\Controllers\ReviewController',[ 'names' => 'route_toursdk_review' ])
	->middleware(['web','auth','verified','CoreMiddleware']);

//Page
Route::resource('/cms/toursdk/page','budisteikul\toursdk\Controllers\PageController',[ 'names' => 'route_toursdk_page' ])
	->middleware(['web','auth','verified','CoreMiddleware']);

//Booking
Route::get('/cms/toursdk/booking/calendar', 'budisteikul\toursdk\Controllers\BookingController@calendar')->middleware(['web','auth','verified','CoreMiddleware']);
Route::get('/cms/toursdk/booking/checkout', 'budisteikul\toursdk\Controllers\BookingController@checkout')->middleware(['web','auth','verified','CoreMiddleware']);
Route::resource('/cms/toursdk/booking','budisteikul\toursdk\Controllers\BookingController',[ 'names' => 'route_toursdk_booking' ])
	->middleware(['web','auth','verified','CoreMiddleware']);


Route::get('/snippets/activity/{activityId}/calendar/json/{year}/{month}', 'budisteikul\toursdk\Controllers\ShoppingcartController@snippetscalendar');
Route::get('/snippets/pdf/invoice/{sessionId}/Invoice-{id}.pdf', 'budisteikul\toursdk\Controllers\ShoppingcartController@invoice');
Route::get('/snippets/pdf/ticket/{sessionId}/Ticket-{id}.pdf', 'budisteikul\toursdk\Controllers\ShoppingcartController@ticket');
Route::post('/snippets/shoppingcart', 'budisteikul\toursdk\Controllers\ShoppingcartController@shoppingcart');
Route::post('/snippets/shoppingcart/checkout', 'budisteikul\toursdk\Controllers\ShoppingcartController@checkout');
Route::post('/snippets/payment', 'budisteikul\toursdk\Controllers\ShoppingcartController@createpayment');
Route::post('/snippets/payment/confirm', 'budisteikul\toursdk\Controllers\ShoppingcartController@confirmpayment');
Route::post('/snippets/promocode', 'budisteikul\toursdk\Controllers\ShoppingcartController@applypromocode');
Route::post('/snippets/promocode/remove', 'budisteikul\toursdk\Controllers\ShoppingcartController@removepromocode');
Route::post('/snippets/activity/remove', 'budisteikul\toursdk\Controllers\ShoppingcartController@removebookingid');
Route::post('/snippets/activity/invoice-preview', 'budisteikul\toursdk\Controllers\ShoppingcartController@snippetsinvoice');
Route::post('/snippets/widget/cart/session/{id}/activity', 'budisteikul\toursdk\Controllers\ShoppingcartController@addshoppingcart');



