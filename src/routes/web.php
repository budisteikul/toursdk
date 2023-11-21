<?php

	// API
	Route::get('/api/home', 'budisteikul\toursdk\Controllers\APIController@home');
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

	//Shoppingcart
	Route::get('/api/activity/{activityId}/calendar/json/{year}/{month}', 'budisteikul\toursdk\Controllers\APIController@snippetscalendar');
	Route::post('/api/activity/invoice-preview', 'budisteikul\toursdk\Controllers\APIController@snippetsinvoice');
	Route::post('/api/activity/remove', 'budisteikul\toursdk\Controllers\APIController@removebookingid');
	Route::post('/api/widget/cart/session/{id}/activity', 'budisteikul\toursdk\Controllers\APIController@addshoppingcart');
	Route::post('/api/shoppingcart', 'budisteikul\toursdk\Controllers\APIController@shoppingcart');
	Route::post('/api/shoppingcart/checkout', 'budisteikul\toursdk\Controllers\APIController@checkout');
	Route::post('/api/promocode', 'budisteikul\toursdk\Controllers\APIController@applypromocode');
	Route::post('/api/promocode/remove', 'budisteikul\toursdk\Controllers\APIController@removepromocode');

	//Checkout
	Route::get('/api/checkout/jscript', 'budisteikul\toursdk\Controllers\APIController@checkout_jscript');

	//Receipt
	Route::get('/api/receipt/jscript', 'budisteikul\toursdk\Controllers\APIController@receipt_jscript');
	Route::get('/api/receipt/{sessionId}/{confirmationCode}', 'budisteikul\toursdk\Controllers\APIController@receipt');

	//Callback Payment
	Route::post('/api/payment/rapyd/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentrapyd');
	Route::post('/api/payment/stripe/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentstripe');
	Route::post('/api/payment/tazapay/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymenttazapay');
	Route::post('/api/payment/paypal/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentpaypal');
	Route::post('/api/payment/midtrans/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentmidtrans');
	Route::post('/api/payment/xendit/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentxendit');
	Route::post('/api/payment/duitku/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentduitku');
	Route::post('/api/payment/finmo/confirm', 'budisteikul\toursdk\Controllers\APIController@confirmpaymentfinmo');

	//Create Payment
	Route::get('/api/payment/stripe/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\APIController@stripe_jscript');
	Route::post('/api/payment/stripe', 'budisteikul\toursdk\Controllers\APIController@createpaymentstripe');

	Route::get('/api/payment/xendit/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\APIController@xendit_jscript');
	Route::post('/api/payment/xendit', 'budisteikul\toursdk\Controllers\APIController@createpaymentxendit');
	
	Route::get('/api/payment/paypal/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\APIController@paypal_jscript');
	Route::post('/api/payment/paypal', 'budisteikul\toursdk\Controllers\APIController@createpaymentpaypal');
	
	Route::get('/api/payment/ovo/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\APIController@ovo_jscript');
	Route::post('/api/payment/ovo', 'budisteikul\toursdk\Controllers\APIController@createpaymentovo');
	
	Route::post('/api/payment', 'budisteikul\toursdk\Controllers\APIController@createpayment');
	
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

	// TEST
	//Route::get('/testaja', 'budisteikul\toursdk\Controllers\APIController@testaja');
	//Route::get('/raillink', 'budisteikul\toursdk\Controllers\APIController@check_raillink');

	


	
