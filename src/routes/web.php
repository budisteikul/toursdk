<?php

	// API
	Route::get('/api/home', 'budisteikul\toursdk\Controllers\APIController@categories');
	Route::get('/api/{sessionId}/navbar', 'budisteikul\toursdk\Controllers\APIController@navbar');
	Route::get('/api/tawkto/{id}', 'budisteikul\toursdk\Controllers\APIController@tawkto');

	//Review
	Route::post('/api/review', 'budisteikul\toursdk\Controllers\APIController@review');
	Route::get('/api/review/count', 'budisteikul\toursdk\Controllers\APIController@review_count');
	Route::get('/api/review/jscript', 'budisteikul\toursdk\Controllers\APIController@review_jscript');

	//Page
	Route::get('/api/page/{slug}', 'budisteikul\toursdk\Controllers\APIController@page');

	//Category
    Route::get('/api/categories', 'budisteikul\toursdk\Controllers\APIController@categories');
    Route::get('/api/category/{slug}', 'budisteikul\toursdk\Controllers\APIController@category');

    //Product
    Route::post('/api/product/add', 'budisteikul\toursdk\Controllers\APIController@product_add');
    Route::post('/api/product/remove', 'budisteikul\toursdk\Controllers\APIController@product_remove');
    Route::get('/api/product/{slug}', 'budisteikul\toursdk\Controllers\APIController@product');
	Route::get('/api/product/{slug}/{sessionId}/product_jscript', 'budisteikul\toursdk\Controllers\APIController@product_jscript');

	//Shoppingcart
	Route::get('/api/activity/{activityId}/calendar/json/{year}/{month}', 'budisteikul\toursdk\Controllers\APIController@snippetscalendar');
	Route::post('/api/activity/invoice-preview', 'budisteikul\toursdk\Controllers\APIController@snippetsinvoice');
	Route::post('/api/activity/remove', 'budisteikul\toursdk\Controllers\APIController@removebookingid');
	Route::post('/api/widget/cart/session/{id}/activity', 'budisteikul\toursdk\Controllers\APIController@addshoppingcart');
	Route::post('/api/shoppingcart', 'budisteikul\toursdk\Controllers\APIController@shoppingcart');
	Route::post('/api/shoppingcart/checkout', 'budisteikul\toursdk\Controllers\APIController@checkout');
	Route::post('/api/promocode', 'budisteikul\toursdk\Controllers\APIController@applypromocode');
	Route::post('/api/promocode/remove', 'budisteikul\toursdk\Controllers\APIController@removepromocode');

	//Receipt
	Route::get('/api/receipt/{sessionId}/{confirmationCode}', 'budisteikul\toursdk\Controllers\APIController@receipt');

	//Disbursmenet
	Route::post('/api/disbursement/oy/confirm/{id}', 'budisteikul\toursdk\Controllers\APIController@confirmdisbursementoy');

	//Confirm Payment
	Route::post('/api/payment/paypal/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentpaypal');
	Route::post('/api/payment/doku/confirm/{id}', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentdoku');
	Route::post('/api/payment/oy/confirm/{id}', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentoy');
	Route::post('/api/payment/midtrans/confirm/{id}', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentmidtrans');

	//Create Payment
	Route::get('/api/payment/paypal/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\APIController@paypal_jscript');
	Route::post('/api/payment/paypal', 'budisteikul\toursdk\Controllers\APIController@createpaymentpaypal');
	Route::get('/api/payment/{payment_type}/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\APIController@payment_jscript');
	Route::post('/api/payment', 'budisteikul\toursdk\Controllers\APIController@createpayment');
	
	//PDF
	Route::get('/api/pdf/manual/{sessionId}/Manual-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@manual');
	Route::get('/api/pdf/invoice/{sessionId}/Invoice-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@invoice');
	Route::get('/api/pdf/ticket/{sessionId}/Ticket-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@ticket');
	Route::get('/api/pdf/instruction/{sessionId}/Instruction-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@instruction');
	Route::get('/api/qrcode/{sessionId}/{id}', 'budisteikul\toursdk\Controllers\APIController@downloadQrcode');

	//Ticket
	Route::post('/api/ticket/check', 'budisteikul\toursdk\Controllers\APIController@ticket_check');
	Route::get('/api/ticket/lastorder/{sessionId}', 'budisteikul\toursdk\Controllers\APIController@last_order');

	// Webhook
	Route::post('/webhook/{webhook_app}', 'budisteikul\toursdk\Controllers\WebhookController@webhook');
	Route::get('/webhook/{webhook_app}', 'budisteikul\toursdk\Controllers\WebhookController@webhook');

	Route::get('/test','budisteikul\toursdk\Controllers\APIController@test');

