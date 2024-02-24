<?php

	// API
	Route::get('/api/index_jscript', 'budisteikul\toursdk\Controllers\APIController@config')->middleware(['SettingMiddleware']);
	Route::get('/api/config', 'budisteikul\toursdk\Controllers\APIController@config')->middleware(['SettingMiddleware']);
	Route::get('/api/{sessionId}/navbar', 'budisteikul\toursdk\Controllers\APIController@navbar')->middleware(['SettingMiddleware']);
	Route::get('/api/tawkto/{id}', 'budisteikul\toursdk\Controllers\APIController@tawkto')->middleware(['SettingMiddleware']);

	//Review
	Route::post('/api/review', 'budisteikul\toursdk\Controllers\APIController@review')->middleware(['SettingMiddleware']);
	Route::get('/api/review/count', 'budisteikul\toursdk\Controllers\APIController@review_count')->middleware(['SettingMiddleware']);
	Route::get('/api/review/jscript', 'budisteikul\toursdk\Controllers\APIController@review_jscript')->middleware(['SettingMiddleware']);

	//Schedule
	Route::post('/api/schedule', 'budisteikul\toursdk\Controllers\APIController@schedule')->middleware(['SettingMiddleware']);
	Route::get('/api/schedule/jscript', 'budisteikul\toursdk\Controllers\APIController@schedule_jscript')->middleware(['SettingMiddleware']);

	//Page
	Route::get('/api/page/{slug}', 'budisteikul\toursdk\Controllers\APIController@page')->middleware(['SettingMiddleware']);

	//Category
    Route::get('/api/categories', 'budisteikul\toursdk\Controllers\APIController@categories')->middleware(['SettingMiddleware']);
    Route::get('/api/category/{slug}', 'budisteikul\toursdk\Controllers\APIController@category')->middleware(['SettingMiddleware']);

    //Product
    Route::post('/api/product/add', 'budisteikul\toursdk\Controllers\APIController@product_add')->middleware(['SettingMiddleware']);
    Route::post('/api/product/remove', 'budisteikul\toursdk\Controllers\APIController@product_remove')->middleware(['SettingMiddleware']);
    Route::get('/api/product/{slug}', 'budisteikul\toursdk\Controllers\APIController@product')->middleware(['SettingMiddleware']);
	Route::get('/api/product/{slug}/{sessionId}/product_jscript', 'budisteikul\toursdk\Controllers\APIController@product_jscript')->middleware(['SettingMiddleware']);





	//Create Payment
	Route::post('/api/payment/checkout', 'budisteikul\toursdk\Controllers\PaymentController@checkout')->middleware(['SettingMiddleware']);

	Route::get('/api/payment/stripe/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@stripe_jscript')->middleware(['SettingMiddleware']);
	Route::post('/api/payment/stripe', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentstripe')->middleware(['SettingMiddleware']);

	Route::get('/api/payment/xendit/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@xendit_jscript')->middleware(['SettingMiddleware']);
	Route::post('/api/payment/xendit', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentxendit')->middleware(['SettingMiddleware']);

	Route::get('/api/payment/paypal/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@paypal_jscript')->middleware(['SettingMiddleware']);
	Route::post('/api/payment/paypal', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentpaypal')->middleware(['SettingMiddleware']);

	Route::get('/api/payment/ovo/jscript/{sessionId}', 'budisteikul\toursdk\Controllers\PaymentController@ovo_jscript')->middleware(['SettingMiddleware']);
	Route::post('/api/payment/ovo', 'budisteikul\toursdk\Controllers\PaymentController@createpaymentovo')->middleware(['SettingMiddleware']);



	//Shoppingcart
	Route::get('/api/activity/{activityId}/calendar/json/{year}/{month}', 'budisteikul\toursdk\Controllers\APIController@snippetscalendar')->middleware(['SettingMiddleware']);
	Route::post('/api/activity/invoice-preview', 'budisteikul\toursdk\Controllers\APIController@snippetsinvoice')->middleware(['SettingMiddleware']);
	Route::post('/api/activity/remove', 'budisteikul\toursdk\Controllers\APIController@removebookingid')->middleware(['SettingMiddleware']);
	Route::post('/api/widget/cart/session/{id}/activity', 'budisteikul\toursdk\Controllers\APIController@addshoppingcart')->middleware(['SettingMiddleware']);
	Route::post('/api/shoppingcart', 'budisteikul\toursdk\Controllers\APIController@shoppingcart')->middleware(['SettingMiddleware']);
	Route::post('/api/promocode', 'budisteikul\toursdk\Controllers\APIController@applypromocode')->middleware(['SettingMiddleware']);
	Route::post('/api/promocode/remove', 'budisteikul\toursdk\Controllers\APIController@removepromocode')->middleware(['SettingMiddleware']);

	//Checkout
	Route::get('/api/checkout/jscript', 'budisteikul\toursdk\Controllers\APIController@checkout_jscript')->middleware(['SettingMiddleware']);

	//Receipt
	Route::get('/api/receipt/jscript', 'budisteikul\toursdk\Controllers\APIController@receipt_jscript')->middleware(['SettingMiddleware']);
	Route::get('/api/receipt/{sessionId}/{confirmationCode}', 'budisteikul\toursdk\Controllers\APIController@receipt')->middleware(['SettingMiddleware']);

	//Cancellation
	Route::post('/api/cancel/{sessionId}/{confirmationCode}', 'budisteikul\toursdk\Controllers\APIController@cancellation')->middleware(['SettingMiddleware']);

	//Callback Payment
	Route::post('/api/payment/stripe/confirm', 'budisteikul\toursdk\Controllers\CallbackController@confirmpaymentstripe')->middleware(['SettingMiddleware']);
	Route::post('/api/payment/paypal/confirm', 'budisteikul\toursdk\Controllers\CallbackController@confirmpaymentpaypal')->middleware(['SettingMiddleware']);
	Route::post('/api/payment/xendit/confirm', 'budisteikul\toursdk\Controllers\CallbackController@confirmpaymentxendit')->middleware(['SettingMiddleware']);
	
	//PDF
	Route::get('/api/pdf/manual/{sessionId}/Manual-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@manual')->middleware(['SettingMiddleware']);
	Route::get('/api/pdf/invoice/{sessionId}/Invoice-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@invoice')->middleware(['SettingMiddleware']);
	Route::get('/api/pdf/ticket/{sessionId}/Ticket-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@ticket')->middleware(['SettingMiddleware']);
	Route::get('/api/pdf/instruction/{sessionId}/Instruction-{id}.pdf', 'budisteikul\toursdk\Controllers\APIController@instruction')->middleware(['SettingMiddleware']);

	//Download
	Route::get('/api/qrcode/{sessionId}/{id}', 'budisteikul\toursdk\Controllers\APIController@downloadQrcode')->middleware(['SettingMiddleware']);

	//Last Order
	Route::get('/api/ticket/{sessionId}/last-order', 'budisteikul\toursdk\Controllers\APIController@last_order')->middleware(['SettingMiddleware']);

	// Webhook
	Route::post('/webhook/{webhook_app}', 'budisteikul\toursdk\Controllers\WebhookController@webhook')->middleware(['SettingMiddleware']);
	Route::get('/webhook/{webhook_app}', 'budisteikul\toursdk\Controllers\WebhookController@webhook')->middleware(['SettingMiddleware']);

	// TASK
	Route::post('/task', 'budisteikul\toursdk\Controllers\TaskController@task')->middleware(['SettingMiddleware']);

	// LOG
	Route::post('/api/log/{identifier}', 'budisteikul\toursdk\Controllers\LogController@log')->middleware(['SettingMiddleware']);

