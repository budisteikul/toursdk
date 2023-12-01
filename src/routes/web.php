<?php

	// API
	Route::get('/api/home', 'budisteikul\toursdk\Controllers\APIController@home');
	Route::get('/api/index_jscript', 'budisteikul\toursdk\Controllers\APIController@index_jscript');
	Route::get('/api/{sessionId}/navbar', 'budisteikul\toursdk\Controllers\APIController@navbar');
	Route::get('/api/footer', 'budisteikul\toursdk\Controllers\APIController@footer');
	Route::get('/api/tawkto/{id}', 'budisteikul\toursdk\Controllers\APIController@tawkto');

	//Review
	Route::post('/api/review', 'budisteikul\toursdk\Controllers\APIController@review');
	Route::get('/api/review/count', 'budisteikul\toursdk\Controllers\APIController@review_count');
	Route::get('/api/review/jscript', 'budisteikul\toursdk\Controllers\APIController@review_jscript');

	//Schedule
	Route::post('/api/schedule', 'budisteikul\toursdk\Controllers\APIController@schedule');
	Route::get('/api/schedule/jscript', 'budisteikul\toursdk\Controllers\APIController@schedule_jscript');

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

	//Create Payment
	Route::post('/api/shoppingcart/checkout', 'budisteikul\toursdk\Controllers\PaymentController@checkout');
	Route::post('/api/payment/checkout', 'budisteikul\toursdk\Controllers\PaymentController@checkout');
	Route::get('/api/payment/stripe/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@stripe_jscript');
	Route::post('/api/payment/stripe', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentstripe');
	Route::get('/api/payment/xendit/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@xendit_jscript');
	Route::post('/api/payment/xendit', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentxendit');
	Route::get('/api/payment/paypal/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@paypal_jscript');
	Route::post('/api/payment/paypal', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentpaypal');
	Route::get('/api/payment/ovo/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@ovo_jscript');
	Route::post('/api/payment/ovo', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentovo');

	//Shoppingcart
	Route::get('/api/activity/{activityId}/calendar/json/{year}/{month}', 'budisteikul\toursdk\Controllers\APIController@snippetscalendar');
	Route::post('/api/activity/invoice-preview', 'budisteikul\toursdk\Controllers\APIController@snippetsinvoice');
	Route::post('/api/activity/remove', 'budisteikul\toursdk\Controllers\APIController@removebookingid');
	Route::post('/api/widget/cart/session/{id}/activity', 'budisteikul\toursdk\Controllers\APIController@addshoppingcart');
	Route::post('/api/shoppingcart', 'budisteikul\toursdk\Controllers\APIController@shoppingcart');
	Route::post('/api/promocode', 'budisteikul\toursdk\Controllers\APIController@applypromocode');
	Route::post('/api/promocode/remove', 'budisteikul\toursdk\Controllers\APIController@removepromocode');

	//Checkout
	Route::get('/api/checkout/jscript', 'budisteikul\toursdk\Controllers\APIController@checkout_jscript');

	//Receipt
	Route::get('/api/receipt/jscript', 'budisteikul\toursdk\Controllers\APIController@receipt_jscript');
	Route::get('/api/receipt/{sessionId}/{confirmationCode}', 'budisteikul\toursdk\Controllers\APIController@receipt');

	//Callback Payment
	Route::post('/api/payment/stripe/confirm', 'budisteikul\toursdk\Controllers\CallbackController@confirmpaymentstripe');
	Route::post('/api/payment/paypal/confirm', 'budisteikul\toursdk\Controllers\CallbackController@confirmpaymentpaypal');
	Route::post('/api/payment/xendit/confirm', 'budisteikul\toursdk\Controllers\CallbackController@confirmpaymentxendit');
	
	//PDF
	Route::get('/api/pdf/manual/{sessionId}/Manual-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@manual');
	Route::get('/api/pdf/invoice/{sessionId}/Invoice-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@invoice');
	Route::get('/api/pdf/ticket/{sessionId}/Ticket-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@ticket');
	Route::get('/api/pdf/instruction/{sessionId}/Instruction-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@instruction');

	//Download
	Route::get('/api/qrcode/{sessionId}/{id}', 'budisteikul\toursdk\Controllers\APIController@downloadQrcode');

	//Last Order
	Route::get('/api/ticket/{sessionId}/last-order', 'budisteikul\toursdk\Controllers\APIController@last_order');

	// Webhook
	Route::post('/webhook/{webhook_app}', 'budisteikul\toursdk\Controllers\WebhookController@webhook');
	Route::get('/webhook/{webhook_app}', 'budisteikul\toursdk\Controllers\WebhookController@webhook');

	// TASK
	Route::post('/task', 'budisteikul\toursdk\Controllers\TaskController@task');

