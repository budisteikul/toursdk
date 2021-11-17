<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Http\Request;

use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\ImageHelper;
use budisteikul\toursdk\Helpers\ProductHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use budisteikul\toursdk\Helpers\MidtransHelper;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Models\ShoppingcartProductDetail;
use budisteikul\toursdk\Models\ShoppingcartQuestion;
use budisteikul\toursdk\Models\ShoppingcartQuestionOption;
use budisteikul\toursdk\Models\ShoppingcartPayment;
use Illuminate\Support\Facades\Mail;
use budisteikul\toursdk\Mail\BookingConfirmedMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Illuminate\Support\Facades\Cache;

class BookingHelper {
	
	public static function webhook_insert_shoppingcart($data)
	{
			$shoppingcart = new Shoppingcart();
			$shoppingcart->booking_status = 'CONFIRMED';
			$shoppingcart->confirmation_code = $data['confirmationCode'];
			if(isset($data['promoCode'])) $shoppingcart->promo_code = $data['promoCode']['code'];
			$bookingChannel = '';
			if(isset($data['affiliate']['title']))
			{
				$bookingChannel = $data['affiliate']['title'];
			}
			else
			{
				$bookingChannel = $data['seller']['title'];
			}
			$shoppingcart->booking_channel = $bookingChannel;
			$shoppingcart->session_id = Uuid::uuid4()->toString();
			$shoppingcart->save();
			
			// main contact questions
			$shoppingcart_question = new ShoppingcartQuestion();
			$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_question->type = 'mainContactDetails';
			$shoppingcart_question->question_id = 'firstName';
			$shoppingcart_question->order = 1;
			$shoppingcart_question->answer = $data['customer']['firstName'];
			$shoppingcart_question->save();
			
			$shoppingcart_question = new ShoppingcartQuestion();
			$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_question->type = 'mainContactDetails';
			$shoppingcart_question->question_id = 'lastName';
			$shoppingcart_question->order = 2;
			$shoppingcart_question->answer = $data['customer']['lastName'];
			$shoppingcart_question->save();
			
			$shoppingcart_question = new ShoppingcartQuestion();
			$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_question->type = 'mainContactDetails';
			$shoppingcart_question->question_id = 'email';
			$shoppingcart_question->order = 3;
			$shoppingcart_question->answer = $data['customer']['email'];
			$shoppingcart_question->save();
			
			$shoppingcart_question = new ShoppingcartQuestion();
			$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_question->type = 'mainContactDetails';
			$shoppingcart_question->question_id = 'phoneNumber';
			$shoppingcart_question->order = 4;
			$shoppingcart_question->answer = $data['customer']['phoneNumberCountryCode'] .' '. $data['customer']['phoneNumber'];
			$shoppingcart_question->save();
			
			// product
			$grand_total = 0;
			$grand_subtotal = 0;
			$grand_discount = 0;

			for($i=0;$i<count($data['activityBookings']);$i++)
			{
				$shoppingcart_product = new ShoppingcartProduct();
				$shoppingcart_product->shoppingcart_id = $shoppingcart->id;
				$shoppingcart_product->booking_id = $data['activityBookings'][$i]['bookingId'];
				$shoppingcart_product->product_confirmation_code = $data['activityBookings'][$i]['productConfirmationCode'];
				$shoppingcart_product->product_id = $data['activityBookings'][$i]['productId'];
				
				//$shoppingcart_product->image = ImageHelper::thumbnail($product);
				if(isset($data['activityBookings'][$i]['activity']['photos'][0]['derived'][0]['url']))
				{
					$shoppingcart_product->image = $data['activityBookings'][$i]['activity']['photos'][0]['derived'][0]['url'];
				}
				

				$shoppingcart_product->title = $data['activityBookings'][$i]['product']['title'];
				$shoppingcart_product->rate = $data['activityBookings'][$i]['rateTitle'];
				$shoppingcart_product->date = ProductHelper::texttodate($data['activityBookings'][$i]['invoice']['dates']);
				$shoppingcart_product->save();
				
				$lineitems = $data['activityBookings'][$i]['invoice']['lineItems'];
				$subtotal_product = 0;
				$total_discount = 0;
				$total_product = 0;
				for($j=0;$j<count($lineitems);$j++)
				{
					
					$itemBookingId = $lineitems[$j]['itemBookingId'];
					$itemBookingId = explode("_",$itemBookingId);
					
					$type_product = '';
					$unitPrice = 'Price per booking';
					
					
					if($itemBookingId[1]!="pickup")
					{
						$type_product = 'product';
						if($lineitems[$j]['title']!="Passengers")
						{
							$unitPrice = $lineitems[$j]['title'];
						}
					}
					
					if($itemBookingId[1]=="pickup"){
						$type_product = "pickup";
					}
					
					if($type_product=="product")
					{
						$shoppingcart_product_detail = new ShoppingcartProductDetail();
						$shoppingcart_product_detail->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_product_detail->type = $type_product;
						$shoppingcart_product_detail->title = $data['activityBookings'][$i]['product']['title'];
						$shoppingcart_product_detail->people = $lineitems[$j]['people'];
						$shoppingcart_product_detail->qty = $lineitems[$j]['quantity'];
						$shoppingcart_product_detail->price = $lineitems[$j]['unitPrice'];
						$shoppingcart_product_detail->unit_price = $unitPrice;
						$subtotal = $lineitems[$j]['unitPrice'] * $shoppingcart_product_detail->qty;
						$discount = $subtotal - ($lineitems[$j]['discountedUnitPrice'] * $shoppingcart_product_detail->qty);
						$total = $subtotal - $discount;
						$shoppingcart_product_detail->currency = $data['currency'];
						$shoppingcart_product_detail->discount = $discount;
						$shoppingcart_product_detail->subtotal = $subtotal;
						$shoppingcart_product_detail->total = $total;
						$shoppingcart_product_detail->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
					if($type_product=="pickup")
					{
						$shoppingcart_product_detail = new ShoppingcartProductDetail();
						$shoppingcart_product_detail->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_product_detail->type = $type_product;
						$shoppingcart_product_detail->title = 'Pick-up and drop-off services';
						$shoppingcart_product_detail->people = $lineitems[$j]['people'];
						$shoppingcart_product_detail->qty = 1;
						$shoppingcart_product_detail->price = $lineitems[$j]['total'];
						$shoppingcart_product_detail->unit_price = $unitPrice;
						$subtotal = $lineitems[$j]['total'];
						$discount = $subtotal - $lineitems[$j]['discountedUnitPrice'];
						$total = $subtotal - $discount;
						$shoppingcart_product_detail->currency = $data['currency'];
						$shoppingcart_product_detail->discount = $discount;
						$shoppingcart_product_detail->subtotal = $subtotal;
						$shoppingcart_product_detail->total = $total;
						$shoppingcart_product_detail->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
				}
				
				ShoppingcartProduct::where('id',$shoppingcart_product->id)->update([
					'currency'=>$data['currency'],
					'subtotal'=>$subtotal_product,
					'discount'=>$total_discount,
					'total'=>$total_product,
					'due_now'=>$total_product
				]);
				
				// activity question
				if(isset($data['activityBookings'][$i]['answers']))
				{
				$order = 1;
				for($k=0;$k<count($data['activityBookings'][$i]['answers']);$k++)
				{
						$shoppingcart_question = new ShoppingcartQuestion();
						$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
						$shoppingcart_question->type = 'activityBookings';
						$shoppingcart_question->booking_id = $data['activityBookings'][$i]['bookingId'];
						$shoppingcart_question->question_id = $data['activityBookings'][$i]['answers'][$k]['id'];
						$shoppingcart_question->label = $data['activityBookings'][$i]['answers'][$k]['question'];
						$shoppingcart_question->order = $order;
						$shoppingcart_question->answer = $data['activityBookings'][$i]['answers'][$k]['answer'];
						$shoppingcart_question->save();
						$order++;
				}
				}
			}
			
			$grand_discount += $total_discount;
			$grand_subtotal += $subtotal_product;
			$grand_total += $total_product;
			

			$shoppingcart->currency = $data['currency'];
			$shoppingcart->subtotal = $grand_subtotal;
			$shoppingcart->discount = $grand_discount;
			$shoppingcart->total = $grand_total;
			$shoppingcart->due_now = $grand_total;
			$shoppingcart->save();

			$shoppingcart_payment = new ShoppingcartPayment();
			$shoppingcart_payment->amount = self::convert_currency($grand_total,$data['currency'],env("PAYPAL_CURRENCY"));

			$shoppingcart_payment->rate = self::convert_currency(1,env("PAYPAL_CURRENCY"),$data['currency']);
			$shoppingcart_payment->rate_from = $data['currency'];
			$shoppingcart_payment->rate_to = env("PAYPAL_CURRENCY");

			$shoppingcart_payment->currency = env("PAYPAL_CURRENCY");
			$shoppingcart_payment->payment_status = 2;
			$shoppingcart_payment->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_payment->save();
			
			return $shoppingcart;
	}
	
	public static function insert_shoppingcart($contents,$id)
	{
		Cache::forget('_'. $id);
		
		$activity = $contents->activityBookings;

		$s_confirmation_code = self::get_ticket();
		$s_session_id = $id;
		$s_booking_channel = 'WEBSITE';
		$s_currency = $contents->customerInvoice->currency;
		$s_promo_code = NULL;
		if(isset($contents->promoCode)) $s_promo_code = $contents->promoCode->code;
		
		$grand_total = 0;
		$grand_subtotal = 0;
		$grand_discount = 0;
		$grand_due_now = 0;
		$grand_due_on_arrival = 0;

		
		for($i=0;$i<count($activity);$i++)
		{
			$product_invoice = $contents->customerInvoice->productInvoices;
			$lineitems = $product_invoice[$i]->lineItems;
			
			
			$sp_product_confirmation_code = $activity[$i]->productConfirmationCode;
			$sp_booking_id = $activity[$i]->id;
			$sp_product_id = $activity[$i]->activity->id;
			$sp_image = NULL;
			if(isset($product_invoice[$i]->product->keyPhoto->derived[2]->url))
			{
				$sp_image = $product_invoice[$i]->product->keyPhoto->derived[2]->url;
			}
			else
			{

				$product = Product::where('bokun_id',$activity[$i]->activity->id)->first();
				if($product)
				{
					$sp_image = ImageHelper::thumbnail($product);
				}
			}

			$sp_title = $activity[$i]->activity->title;
			$sp_rate = $activity[$i]->rate->title;
			$sp_currency = $contents->customerInvoice->currency;
			$sp_date = ProductHelper::texttodate($product_invoice[$i]->dates);

			$subtotal_product = 0;
			$total_discount = 0;
			$total_product = 0;

			$ShoppingcartProductDetails = array();
			for($z=0;$z<count($lineitems);$z++)
			{
					$itemBookingId = $lineitems[$z]->itemBookingId;
					$itemBookingId = explode("_",$itemBookingId);
					
					$type_product = 'product';
					if($lineitems[$z]->people==0)
					{
						$type_product = "extra";
					}
					if($itemBookingId[1]=="pickup")
					{
						$type_product = "pickup";
					}

					$unitPrice = 'Price per booking';
					if($lineitems[$z]->title!="Passengers")
					{
						$unitPrice = $lineitems[$z]->title;
					}
					

					if($type_product=="product")
					{
						
						$spd_type = $type_product;
						$spd_title = $activity[$i]->activity->title;
						$spd_people = $lineitems[$z]->people;
						$spd_qty = $lineitems[$z]->quantity;
						$spd_price = $lineitems[$z]->unitPrice;
						$spd_unit_price = $unitPrice;
						$spd_currency = $contents->customerInvoice->currency;
						$subtotal = $lineitems[$z]->unitPrice * $lineitems[$z]->quantity;
						$discount = $subtotal - ($lineitems[$z]->discountedUnitPrice * $lineitems[$z]->quantity);
						$total = $subtotal - $discount;

						//===============================================================
						
						$ShoppingcartProductDetails[] = (object) array(
							'type' => $spd_type,
							'title' => $spd_title,
							'people' => $spd_people,
							'qty' => $spd_qty,
							'price' => $spd_price,
							'unit_price' => $spd_unit_price,
							'discount' => $discount,
							'subtotal' => $subtotal,
							'currency' => $spd_currency,
							'total' => $total
						);
						//===============================================================
						
						

						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}

					if($type_product=="extra")
					{
						
						$spd_type = $type_product;
						$spd_title = $activity[$i]->activity->title;
						$spd_people = $lineitems[$z]->people;
						$spd_qty = $lineitems[$z]->quantity;
						$spd_price = $lineitems[$z]->unitPrice;
						$spd_unit_price = $unitPrice;
						$spd_currency = $contents->customerInvoice->currency;
						$subtotal = $lineitems[$z]->unitPrice * $lineitems[$z]->quantity;
						$discount = $subtotal - ($lineitems[$z]->discountedUnitPrice * $lineitems[$z]->quantity);
						$total = $subtotal - $discount;

						//===============================================================
						
						$ShoppingcartProductDetails[] = (object) array(
							'type' => $spd_type,
							'title' => $spd_title,
							'people' => $spd_people,
							'qty' => $spd_qty,
							'price' => $spd_price,
							'unit_price' => $spd_unit_price,
							'discount' => $discount,
							'subtotal' => $subtotal,
							'currency' => $spd_currency,
							'total' => $total
						);
						//===============================================================
						
						

						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
					if($type_product=="pickup")
					{

						$spd_type = $type_product;
						$spd_title = 'Pick-up and drop-off services';
						$spd_people = $lineitems[$z]->people;
						$spd_qty = 1;
						$spd_price = $lineitems[$z]->total;
						$spd_unit_price = $unitPrice;
						$spd_currency = $contents->customerInvoice->currency;
						$subtotal = $lineitems[$z]->total;
						$discount = $subtotal - $lineitems[$z]->discountedUnitPrice;
						$total = $subtotal - $discount;

						//===============================================================
						
						$ShoppingcartProductDetails[] = (object) array(
							'type' => $spd_type,
							'title' => $spd_title,
							'people' => $spd_people,
							'qty' => $spd_qty,
							'price' => $spd_price,
							'unit_price' => $spd_unit_price,
							'discount' => $discount,
							'subtotal' => $subtotal,
							'currency' => $spd_currency,
							'total' => $total
						);
						//===============================================================
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}	
					
			}
			

			$deposit = self::get_deposit($activity[$i]->activity->id,$total_product);
			
			
			//=====================================================
			
			$ShoppingcartProducts[] = (object) array(
				'product_confirmation_code' => $sp_product_confirmation_code,
				'booking_id' => $sp_booking_id,
				'product_id' => $sp_booking_id,
				'image' => $sp_image,
				'title' => $sp_title,
				'rate' => $sp_rate,
				'currency' =>  $sp_currency,
				'date' => $sp_date,
				'subtotal' => $subtotal_product,
				'discount' => $total_discount,
				'total' => $total_product,
				'due_now' => $deposit->due_now,
				'due_on_arrival' => $deposit->due_on_arrival,
				'product_details' => $ShoppingcartProductDetails,

			);
			//=====================================================

			$grand_discount += $total_discount;
			$grand_subtotal += $subtotal_product;
			$grand_total += $total_product;
			$grand_due_now += $deposit->due_now;
			$grand_due_on_arrival += $deposit->due_on_arrival;
		}

		//===================================================
		
		// QUESTION ==============================================================================
		// Main Question ====
		$questions = BokunHelper::get_questionshoppingcart($id);

		$mainContactDetails = $questions->mainContactQuestions;
		$order = 1;

		$ShoppingcartQuestions = array();
		foreach($mainContactDetails as $mainContactDetail)
		{
			
			$scq_booking_id = NULL;
			$scq_type = 'mainContactDetails';
			$scq_question_id = $mainContactDetail->questionId;
			$scq_label = $mainContactDetail->label;
			$scq_help = NULL;
			$scq_data_type = $mainContactDetail->dataType;
			$scq_data_format = NULL;
			if(isset($mainContactDetail->dataFormat)) $scq_data_format = $mainContactDetail->dataFormat;
			$scq_required = $mainContactDetail->required;
			$scq_select_option = $mainContactDetail->selectFromOptions;
			$scq_select_multiple = $mainContactDetail->selectMultiple;
			$scq_order = $order;
			$order += 1;

			$ShoppingcartQuestionOptions = array();
			if($mainContactDetail->selectFromOptions=="true")
			{
				$order_option = 1;
				foreach($mainContactDetail->answerOptions as $answerOption)
				{
					$scqd_label = $answerOption->label;
					$scqd_value = $answerOption->value;
					$scqd_order = $order_option;
					$order_option += 1;

					$ShoppingcartQuestionOptions[] = (object) array(
						'label' => $scqd_label,
						'value' => $scqd_value,
						'order' => $scqd_order,
					);

					
				}
			}

			$ShoppingcartQuestions[] = (object) array(
				'type' => $scq_type,
				'question_id' => $scq_question_id,
				'booking_id' => $scq_booking_id,
				'label' => $scq_label,
				'help' => $scq_help,
				'data_type' => $scq_data_type,
				'data_format' => $scq_data_format,
				'required' => $scq_required,
				'select_option' => $scq_select_option,
				'select_multiple' => $scq_select_multiple,
				'order' => $scq_order,
				'answer' => '',
				'question_options' => $ShoppingcartQuestionOptions
			);
		}
		
		
		//===========================================================================
		$order = 1;
		for($ii = 0; $ii < count($questions->checkoutOptions); $ii++){
			// Pickup Question
			if(isset($questions->checkoutOptions[$ii]->pickup->questions)){
					$activityBookingId = $questions->checkoutOptions[$ii]->activityBookingDetail->activityBookingId;
					$pickupQuestion = $questions->checkoutOptions[$ii]->pickup->questions[0];

					$scq_type = 'pickupQuestions';
					$scq_booking_id = $activityBookingId;
					$scq_question_id = $pickupQuestion->questionId;
					$scq_label = $pickupQuestion->label;
					$scq_help = NULL;
					$scq_data_type = $pickupQuestion->dataType;
					$scq_data_format = NULL;
					$scq_required = $pickupQuestion->required;
					$scq_select_option = $pickupQuestion->selectFromOptions;
					$scq_select_multiple = $pickupQuestion->selectMultiple;
					$scq_order = $order;
					$order += 1;

					$ShoppingcartQuestions[] = (object) array(
						'type' => $scq_type,
						'question_id' => $scq_question_id,
						'booking_id' => $scq_booking_id,
						'label' => $scq_label,
						'help' => $scq_help,
						'data_type' => $scq_data_type,
						'data_format' => $scq_data_format,
						'required' => $scq_required,
						'select_option' => $scq_select_option,
						'select_multiple' => $scq_select_multiple,
						'order' => $scq_order,
						'answer' => '',
						'question_options' => array()
					);

			}
			// ActivityBookings question
			if(isset($questions->checkoutOptions[$ii]->perBookingQuestions)){
				$activityBookingId = $questions->checkoutOptions[$ii]->activityBookingDetail->activityBookingId;
				for($jj = 0; $jj < count($questions->checkoutOptions[$ii]->perBookingQuestions); $jj++)
				{
					$activityBookingQuestion = $questions->checkoutOptions[$ii]->perBookingQuestions[$jj];

					$scq_type = 'activityBookings';
					$scq_booking_id = $activityBookingId;
					$scq_question_id =  $activityBookingQuestion->questionId;
					$scq_label = $activityBookingQuestion->label;
					$scq_help = NULL;
					if(isset($activityBookingQuestion->help)) $scq_help = $activityBookingQuestion->help;
					$scq_data_type = $activityBookingQuestion->dataType;
					$scq_data_format = NULL;
					if(isset($activityBookingQuestion->dataFormat)) $scq_data_format = $activityBookingQuestion->dataFormat;
					$scq_required = $activityBookingQuestion->required;
					$scq_select_option = $activityBookingQuestion->selectFromOptions;
					$scq_select_multiple = $activityBookingQuestion->selectMultiple;
					$scq_order = $order;
					$order += 1;

					

					$ShoppingcartQuestionOptions = array();
					if($activityBookingQuestion->selectFromOptions=="true")
					{
						$order_option = 1;
						foreach($activityBookingQuestion->answerOptions as $answerOption)
						{
							
							

							$scqd_label = $answerOption->label;
							$scqd_value = $answerOption->value;
							$scqd_order = $order_option;

							$ShoppingcartQuestionOptions[] = (object) array(
								'label' => $scqd_label,
								'value' => $scqd_value,
								'order' => $scqd_order,
							);

							$order_option += 1;
						}
					}

					$ShoppingcartQuestions[] = (object) array(
						'type' => $scq_type,
						'question_id' => $scq_question_id,
						'booking_id' => $scq_booking_id,
						'label' => $scq_label,
						'help' => $scq_help,
						'data_type' => $scq_data_type,
						'data_format' => $scq_data_format,
						'required' => $scq_required,
						'select_option' => $scq_select_option,
						'select_multiple' => $scq_select_multiple,
						'order' => $scq_order,
						'answer' => '',
						'question_options' => $ShoppingcartQuestionOptions
					);

				}
			}
		}

		
		$ShoppingCart = (object)[
			'session_id' => $s_session_id,
			'booking_channel' => $s_booking_channel,
			'confirmation_code' => $s_confirmation_code,
			'currency' => $s_currency,
			'promo_code' => $s_promo_code,
			'subtotal' => $grand_subtotal,
			'discount' => $grand_discount,
			'total' => $grand_total,
			'due_now' => $grand_due_now,
			'due_on_arrival' => $grand_due_on_arrival,
			'products' => $ShoppingcartProducts,
			'questions' => $ShoppingcartQuestions,
		];
		
		Cache::add('_'. $id, $ShoppingCart, 172800);
	}
	


	public static function update_shoppingcart($contents,$id)
	{
		$activity = $contents->activityBookings;

		$shoppingcart = Cache::get('_'. $id);

		$shoppingcart->session_id = $id;

		$shoppingcart->currency = $contents->customerInvoice->currency;

		if(isset($contents->promoCode))
		{
			$shoppingcart->promo_code = $contents->promoCode->code;
		}
		else
		{
			$shoppingcart->promo_code = null;
		}
		
		unset($shoppingcart->products);
		

		$grand_total = 0;
		$grand_subtotal = 0;
		$grand_discount = 0;
		$grand_due_now = 0;
		$grand_due_on_arrival = 0;

		$ShoppingcartProducts = array();
		for($i=0;$i<count($activity);$i++)
		{
			$product_invoice = $contents->customerInvoice->productInvoices;
			$lineitems = $product_invoice[$i]->lineItems;
			
			$sp_product_confirmation_code = $activity[$i]->productConfirmationCode;
			$sp_booking_id = $activity[$i]->id;
			$sp_product_id = $activity[$i]->activity->id;
			$sp_image = NULL;
			if(isset($product_invoice[$i]->product->keyPhoto->derived[2]->url))
			{
				$sp_image = $product_invoice[$i]->product->keyPhoto->derived[2]->url;
			}
			else
			{
				$product = Product::where('bokun_id',$activity[$i]->activity->id)->first();
				if($product)
				{
					$sp_image = ImageHelper::thumbnail($product);
				}
			}
			$sp_title = $activity[$i]->activity->title;
			$sp_rate = $activity[$i]->rate->title;
			$sp_currency = $contents->customerInvoice->currency;
			$sp_date = ProductHelper::texttodate($product_invoice[$i]->dates);

			

			$subtotal_product = 0;
			$total_discount = 0;
			$total_product = 0;


			$ShoppingcartProductDetails = array();
			for($z=0;$z<count($lineitems);$z++)
			{
					$itemBookingId = $lineitems[$z]->itemBookingId;
					$itemBookingId = explode("_",$itemBookingId);
					
					$type_product = 'product';
					if($lineitems[$z]->people==0)
					{
						$type_product = 'extra';
					}
					if($itemBookingId[1]=="pickup"){
						$type_product = "pickup";
					}

					$unitPrice = 'Price per booking';
					if($lineitems[$z]->title!="Passengers")
					{
						$unitPrice = $lineitems[$z]->title;
					}

					if($type_product=="product")
					{
						
						$spd_type = $type_product;
						$spd_title = $activity[$i]->activity->title;
						$spd_people = $lineitems[$z]->people;
						$spd_qty = $lineitems[$z]->quantity;
						$spd_price = $lineitems[$z]->unitPrice;
						$spd_unit_price = $unitPrice;
						$spd_currency = $contents->customerInvoice->currency;
						$subtotal = $lineitems[$z]->unitPrice * $lineitems[$z]->quantity;
						$discount = $subtotal - ($lineitems[$z]->discountedUnitPrice * $lineitems[$z]->quantity);
						$total = $subtotal - $discount;

						$ShoppingcartProductDetails[] = (object) array(
							'type' => $spd_type,
							'title' => $spd_title,
							'people' => $spd_people,
							'qty' => $spd_qty,
							'price' => $spd_price,
							'unit_price' => $spd_unit_price,
							'discount' => $discount,
							'subtotal' => $subtotal,
							'currency' => $spd_currency,
							'total' => $total
						);
						
						

						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}

					if($type_product=="extra")
					{
						
						$spd_type = $type_product;
						$spd_title = $activity[$i]->activity->title;
						$spd_people = $lineitems[$z]->people;
						$spd_qty = $lineitems[$z]->quantity;
						$spd_price = $lineitems[$z]->unitPrice;
						$spd_unit_price = $unitPrice;
						$spd_currency = $contents->customerInvoice->currency;
						$subtotal = $lineitems[$z]->unitPrice * $lineitems[$z]->quantity;
						$discount = $subtotal - ($lineitems[$z]->discountedUnitPrice * $lineitems[$z]->quantity);
						$total = $subtotal - $discount;

						$ShoppingcartProductDetails[] = (object) array(
							'type' => $spd_type,
							'title' => $spd_title,
							'people' => $spd_people,
							'qty' => $spd_qty,
							'price' => $spd_price,
							'unit_price' => $spd_unit_price,
							'discount' => $discount,
							'subtotal' => $subtotal,
							'currency' => $spd_currency,
							'total' => $total
						);
						
						

						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
					if($type_product=="pickup")
					{
						$spd_type = $type_product;
						$spd_title = 'Pick-up and drop-off services';
						$spd_people = $lineitems[$z]->people;
						$spd_qty = 1;
						$spd_price = $lineitems[$z]->total;
						$spd_unit_price = $unitPrice;
						$spd_currency = $contents->customerInvoice->currency;
						$subtotal = $lineitems[$z]->total;
						$discount = $subtotal - $lineitems[$z]->discountedUnitPrice;
						$total = $subtotal - $discount;
						
						$ShoppingcartProductDetails[] = (object) array(
							'type' => $spd_type,
							'title' => $spd_title,
							'people' => $spd_people,
							'qty' => $spd_qty,
							'price' => $spd_price,
							'unit_price' => $spd_unit_price,
							'discount' => $discount,
							'subtotal' => $subtotal,
							'currency' => $spd_currency,
							'total' => $total
						);

						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}	
					
			}
			
			
			$deposit = self::get_deposit($activity[$i]->activity->id,$total_product);
			

			
			$ShoppingcartProducts[] = (object) array(
				'product_confirmation_code' => $sp_product_confirmation_code,
				'booking_id' => $sp_booking_id,
				'product_id' => $sp_booking_id,
				'image' => $sp_image,
				'title' => $sp_title,
				'rate' => $sp_rate,
				'currency' =>  $sp_currency,
				'date' => $sp_date,
				'subtotal' => $subtotal_product,
				'discount' => $total_discount,
				'total' => $total_product,
				'due_now' => $deposit->due_now,
				'due_on_arrival' => $deposit->due_on_arrival,
				'product_details' => $ShoppingcartProductDetails,

			);

			$grand_discount += $total_discount;
			$grand_subtotal += $subtotal_product;
			$grand_total += $total_product;
			$grand_due_now += $deposit->due_now;
			$grand_due_on_arrival += $deposit->due_on_arrival;
		}
		

		$shoppingcart->products = $ShoppingcartProducts;
		$shoppingcart->subtotal = $grand_subtotal;
		$shoppingcart->discount = $grand_discount;
		$shoppingcart->total = $grand_total;
		$shoppingcart->due_now = $grand_due_now;
		$shoppingcart->due_on_arrival = $grand_due_on_arrival;
		

		
		//===============================================

		$questions = BokunHelper::get_questionshoppingcart($id);
		foreach($shoppingcart->questions as $key => $question)
		{
			if($question->type=='activityBookings')
			{
				
				unset($shoppingcart->questions[$key]);
			}
			if($question->type=='pickupQuestions')
			{
				unset($shoppingcart->questions[$key]);
			}
		}
		
		$ShoppingcartQuestions = $shoppingcart->questions;

		//===========================================================================
		$order = 1;
		for($ii = 0; $ii < count($questions->checkoutOptions); $ii++){
			// Pickup Question
			if(isset($questions->checkoutOptions[$ii]->pickup->questions)){
					$activityBookingId = $questions->checkoutOptions[$ii]->activityBookingDetail->activityBookingId;
					$pickupQuestion = $questions->checkoutOptions[$ii]->pickup->questions[0];

					$scq_type = 'pickupQuestions';
					$scq_booking_id = $activityBookingId;
					$scq_question_id = $pickupQuestion->questionId;
					$scq_label = $pickupQuestion->label;
					$scq_help = NULL;
					$scq_data_type = $pickupQuestion->dataType;
					$scq_data_format = NULL;
					$scq_required = $pickupQuestion->required;
					$scq_select_option = $pickupQuestion->selectFromOptions;
					$scq_select_multiple = $pickupQuestion->selectMultiple;
					$scq_order = $order;
					$order += 1;

					$ShoppingcartQuestions[] = (object) array(
						'type' => $scq_type,
						'question_id' => $scq_question_id,
						'booking_id' => $scq_booking_id,
						'label' => $scq_label,
						'help' => $scq_help,
						'data_type' => $scq_data_type,
						'data_format' => $scq_data_format,
						'required' => $scq_required,
						'select_option' => $scq_select_option,
						'select_multiple' => $scq_select_multiple,
						'order' => $scq_order,
						'answer' => '',
						'question_options' => array()
					);

			}

			// ActivityBookings question
			if(isset($questions->checkoutOptions[$ii]->perBookingQuestions)){
				$activityBookingId = $questions->checkoutOptions[$ii]->activityBookingDetail->activityBookingId;
				//ShoppingcartQuestion::where('booking_id',$activityBookingId)->where('type','activityBookings')->delete();
				for($jj = 0; $jj < count($questions->checkoutOptions[$ii]->perBookingQuestions); $jj++)
				{
					$activityBookingQuestion = $questions->checkoutOptions[$ii]->perBookingQuestions[$jj];
					
					$scq_type = 'activityBookings';
					$scq_booking_id = $activityBookingId;
					$scq_question_id =  $activityBookingQuestion->questionId;
					$scq_label = $activityBookingQuestion->label;
					$scq_help = NULL;
					if(isset($activityBookingQuestion->help)) $scq_help = $activityBookingQuestion->help;
					$scq_data_type = $activityBookingQuestion->dataType;
					$scq_data_format = NULL;
					if(isset($activityBookingQuestion->dataFormat)) $scq_data_format = $activityBookingQuestion->dataFormat;
					$scq_required = $activityBookingQuestion->required;
					$scq_select_option = $activityBookingQuestion->selectFromOptions;
					$scq_select_multiple = $activityBookingQuestion->selectMultiple;
					$scq_order = $order;
					$order += 1;

					$ShoppingcartQuestionOptions = array();
					if($activityBookingQuestion->selectFromOptions=="true")
					{
						$order_option = 1;
						foreach($activityBookingQuestion->answerOptions as $answerOption)
						{
							
							$scqd_label = $answerOption->label;
							$scqd_value = $answerOption->value;
							$scqd_order = $order_option;

							$ShoppingcartQuestionOptions[] = (object) array(
								'label' => $scqd_label,
								'value' => $scqd_value,
								'order' => $scqd_order,
							);

							$order_option += 1;
						}
					}

					$ShoppingcartQuestions[] = (object) array(
						'type' => $scq_type,
						'question_id' => $scq_question_id,
						'booking_id' => $scq_booking_id,
						'label' => $scq_label,
						'help' => $scq_help,
						'data_type' => $scq_data_type,
						'data_format' => $scq_data_format,
						'required' => $scq_required,
						'select_option' => $scq_select_option,
						'select_multiple' => $scq_select_multiple,
						'order' => $scq_order,
						'answer' => '',
						'question_options' => $ShoppingcartQuestionOptions
					);

				}
			}
		}
		$shoppingcart->questions = $ShoppingcartQuestions;
		
		//===========================================
		Cache::forget('_'. $id);
		Cache::add('_'. $id, $shoppingcart, 172800);
		//===========================================
	}
	

	public static function get_deposit($bokunId,$amount)
	{
		$due_now = 0;
		$due_on_arrival = 0;
		$dataObj = new \stdClass();
		$product = Product::where('bokun_id',$bokunId)->first();
		if($product->deposit_amount==0)
		{
			$dataObj->due_now = $amount;
			$dataObj->due_on_arrival = 0;
		}
		else
		{
			if($product->deposit_percentage)
			{
				
				$dataObj->due_now = $amount * $product->deposit_amount / 100;
				$dataObj->due_on_arrival = $amount - $dataObj->due_now;
			}
			else
			{
				$dataObj->due_now = $product->deposit_amount;
				$dataObj->due_on_arrival = $amount - $dataObj->due_now;
			}
		}
			
		return $dataObj;
	}


	public static function get_shoppingcart($id,$action="insert",$contents)
	{

		if($action=="insert")
			{
				self::insert_shoppingcart($contents,$id);
			}
		if($action=="update")
			{
				self::update_shoppingcart($contents,$id);
			}
			
	}
	
	

	public static function shoppingcart_mail($shoppingcart)
	{
		$email = $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer;
		if($email!="")
		{
			Mail::to($email)->send(new BookingConfirmedMail($shoppingcart));
		}
		Mail::to(env('MAIL_FROM_ADDRESS'))->send(new BookingConfirmedMail($shoppingcart));
	}

	public static function shoppingcart_clear($sessionId)
	{
		BokunHelper::get_removepromocode($sessionId);
		$shoppingcart = Cache::get('_'.$sessionId);
		foreach($shoppingcart->products as $product)
		{
			BokunHelper::get_removeactivity($sessionId,$product->booking_id);
		}
		Cache::forget('_'.$sessionId);
        return $shoppingcart;
	}

	public static function check_question_json($sessionId,$data)
	{
		
		$status = true;
		$array = array();

		$shoppingcart = Cache::get('_'.$sessionId);

		
		foreach($shoppingcart->questions as $question)
		{
				if($question->required)
            	{
            		$rules = array('data' => 'required');
            		$inputs = array(
    						'data' => $data['questions'][$question->question_id]
							);
            		$validator = Validator::make($inputs, $rules);
            		if($validator->fails()) {
						$status = false;
						$array[$question->question_id] = array($question->label .' field is required.');
					}
            	}

            	if($status)
				{
					if($question->data_format=="EMAIL_ADDRESS")
						{
							$rules = array('email' => 'email');
							$inputs = array(
    						'email' => $data['questions'][$question->question_id]
							);
							$validator = Validator::make($inputs, $rules);
							if($validator->fails()) {
    							$status = false;
								$array[$question->question_id] = array('Email format not valid.');
							}
						}
				}
		}

		
        return $array;
	}

	public static function save_question_json($sessionId,$data)
	{
		
		$shoppingcart = Cache::get('_'.$sessionId);
		
		foreach($shoppingcart->questions as $question)
            {
            	
            	foreach ($data['questions'] as $key => $value) {
    				if($question->question_id==$key)
    				{
    					$question->answer = $value;
    				}
				}
                
                if($question->select_option)
                {

                	foreach($question->question_options as $question_option)
                	{
                		if($question_option->value==$data['questions'][$question->question_id])
                		{
                			$question_option->answer = 1;
                		}
                		else
                		{
                			$question_option->answer = 0;
                		}
                	}
                    
                }
                
            }


        Cache::forget('_'.$sessionId);
		Cache::add('_'.$sessionId, $shoppingcart, 172800);

        return $shoppingcart;
	}

	public static function confirm_transaction($sessionId)
	{
		$shoppingcart_json = Cache::get('_'. $sessionId);
		
		$shoppingcart = new Shoppingcart();
		$shoppingcart->booking_status = $shoppingcart_json->booking_status;
		$shoppingcart->session_id = $shoppingcart_json->session_id;
		$shoppingcart->booking_channel = $shoppingcart_json->booking_channel;
		$shoppingcart->confirmation_code = $shoppingcart_json->confirmation_code;
		$shoppingcart->promo_code = $shoppingcart_json->promo_code;
		$shoppingcart->currency = $shoppingcart_json->currency;
		$shoppingcart->subtotal = $shoppingcart_json->subtotal;
		$shoppingcart->discount = $shoppingcart_json->discount;
		$shoppingcart->total = $shoppingcart_json->total;
		$shoppingcart->due_now = $shoppingcart_json->due_now;
		$shoppingcart->due_on_arrival = $shoppingcart_json->due_on_arrival;
		$shoppingcart->save();

		foreach($shoppingcart_json->products as $product)
		{
			$shoppingcart_product = new ShoppingcartProduct();
			$shoppingcart_product->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_product->booking_id = $product->booking_id;
			$shoppingcart_product->product_confirmation_code = $product->product_confirmation_code;
			$shoppingcart_product->product_id = $product->product_id;
			$shoppingcart_product->image = $product->image;
			$shoppingcart_product->title = $product->title;
			$shoppingcart_product->rate = $product->rate;
			$shoppingcart_product->date = $product->date;
			$shoppingcart_product->currency = $product->currency;
			$shoppingcart_product->subtotal = $product->subtotal;
			$shoppingcart_product->discount = $product->discount;
			$shoppingcart_product->total = $product->total;
			$shoppingcart_product->due_now = $product->due_now;
			$shoppingcart_product->due_on_arrival = $product->due_on_arrival;
			$shoppingcart_product->save();
			
			foreach($product->product_details as $product_detail)
			{
				$shoppingcart_product_detail = new ShoppingcartProductDetail();
				$shoppingcart_product_detail->shoppingcart_product_id = $shoppingcart_product->id;
				$shoppingcart_product_detail->type = $product_detail->type;
				$shoppingcart_product_detail->title = $product_detail->title;
				$shoppingcart_product_detail->people = $product_detail->people;
				$shoppingcart_product_detail->qty = $product_detail->qty;
				$shoppingcart_product_detail->price = $product_detail->price;
				$shoppingcart_product_detail->unit_price = $product_detail->unit_price;
				$shoppingcart_product_detail->currency = $product_detail->currency;
				$shoppingcart_product_detail->subtotal = $product_detail->subtotal;
				$shoppingcart_product_detail->discount = $product_detail->discount;
				$shoppingcart_product_detail->total = $product_detail->total;
				$shoppingcart_product_detail->save();
			}
		}

		foreach($shoppingcart_json->questions as $question)
		{
			$shoppingcart_question = new ShoppingcartQuestion();
			$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_question->type = $question->type;
			$shoppingcart_question->booking_id = $question->booking_id;
			$shoppingcart_question->question_id = $question->question_id;
			$shoppingcart_question->label = $question->label;
			$shoppingcart_question->data_type = $question->data_type;
			$shoppingcart_question->data_format = $question->data_format;
			$shoppingcart_question->required = $question->required;
			$shoppingcart_question->select_option = $question->select_option;
			$shoppingcart_question->select_multiple = $question->select_multiple;
			$shoppingcart_question->help = $question->help;
			$shoppingcart_question->order = $question->order;
			$shoppingcart_question->answer = $question->answer;
			$shoppingcart_question->save();

			foreach($question->question_options as $question_option)
			{
				$shoppingcart_question_option = new ShoppingcartQuestionOption();
				$shoppingcart_question_option->shoppingcart_question_id = $shoppingcart_question->id;
				$shoppingcart_question_option->label = $question_option->label;
				$shoppingcart_question_option->value = $question_option->value;
				$shoppingcart_question_option->order = $question_option->order;
				$shoppingcart_question_option->answer = $question_option->answer;
				$shoppingcart_question_option->save();
				
			}
		}

		$shoppingcart_payment = new ShoppingcartPayment();
		$shoppingcart_payment->shoppingcart_id = $shoppingcart->id;
		$shoppingcart_payment->payment_provider = $shoppingcart_json->payment->payment_provider;
		$shoppingcart_payment->payment_type = $shoppingcart_json->payment->payment_type;
		$shoppingcart_payment->bank_name = $shoppingcart_json->payment->bank_name;
		$shoppingcart_payment->bank_code = $shoppingcart_json->payment->bank_code;
		$shoppingcart_payment->va_number = $shoppingcart_json->payment->va_number;
		$shoppingcart_payment->order_id = $shoppingcart_json->payment->order_id;
		$shoppingcart_payment->authorization_id = $shoppingcart_json->payment->authorization_id;
		$shoppingcart_payment->amount = $shoppingcart_json->payment->amount;
		$shoppingcart_payment->currency = $shoppingcart_json->payment->currency;
		$shoppingcart_payment->rate = $shoppingcart_json->payment->rate;
		$shoppingcart_payment->rate_from = $shoppingcart_json->payment->rate_from;
		$shoppingcart_payment->rate_to = $shoppingcart_json->payment->rate_to;
		$shoppingcart_payment->payment_status = $shoppingcart_json->payment->payment_status;
		$shoppingcart_payment->save();

		return $shoppingcart;
		
	}

	public static function confirm_booking($sessionId)
	{
		$shoppingcart = Cache::get('_'. $sessionId);
        $shoppingcart->booking_status = "CONFIRMED";
        
        
        Cache::forget('_'. $sessionId);
        Cache::add('_'. $sessionId, $shoppingcart, 172800);

        BokunHelper::get_confirmBooking($sessionId);

        return self::confirm_transaction($sessionId);
	}

	public static function create_payment($sessionId,$payment_type="none")
	{
		$shoppingcart = Cache::get('_'. $sessionId);
		$shoppingcart->confirmation_code = self::get_ticket();
		
		
		if($payment_type=="midtrans")
		{
				
				
				$response = MidtransHelper::createOrder($shoppingcart);
				//print_r($response);
				
				$ShoppingcartPayment = (object) array(
					'payment_provider' => 'midtrans',
					'payment_type' => $response->payment_type,
					'bank_name' => $response->bank_name,
					'bank_code' => $response->bank_code,
					'va_number' => $response->va_number,
					'snaptoken' => $response->snaptoken,
					'order_id' => NULL,
					'authorization_id' => NULL,
					'amount' => self::convert_currency($shoppingcart->due_now,$shoppingcart->currency,"IDR"),
					'currency' => 'IDR',
					'rate' => 1,
					'rate_from' => NULL,
					'rate_to' => NULL,
					'payment_status' => 4,
				);
				//print_r($response);
				//exit();
				$shoppingcart->payment = $ShoppingcartPayment;
				Cache::forget('_'. $sessionId);
				Cache::add('_'. $sessionId, $shoppingcart, 172800);
		}
		else if($payment_type=="paypal")
		{
			$ShoppingcartPayment = (object) array(
				'payment_provider' => 'paypal',
				'payment_type' => NULL,
				'bank_name' => NULL,
				'bank_code' => NULL,
				'va_number' => NULL,
				'snaptoken' => NULL,
				'order_id' => NULL,
				'authorization_id' => NULL,
				'amount' => self::convert_currency($shoppingcart->due_now,$shoppingcart->currency,env("PAYPAL_CURRENCY")),
				'currency' => env("PAYPAL_CURRENCY"),
				'rate' => self::convert_currency(1,env("PAYPAL_CURRENCY"),$shoppingcart->currency),
				'rate_from' => $shoppingcart->currency,
				'rate_to' => env("PAYPAL_CURRENCY"),
				'payment_status' => 0,
			);
			
			$shoppingcart->payment = $ShoppingcartPayment;

			Cache::forget('_'. $sessionId);
			Cache::add('_'. $sessionId, $shoppingcart, 172800);
			
			
			$shoppingcart = Cache::get('_'. $sessionId);
			$response = PaypalHelper::createOrder($shoppingcart);
		}
		else
		{
			$ShoppingcartPayment = (object) array(
				'payment_provider' => 'none',
				'payment_type' => NULL,
				'bank_name' => NULL,
				'bank_code' => NULL,
				'va_number' => NULL,
				'snaptoken' => NULL,
				'order_id' => NULL,
				'authorization_id' => NULL,
				'amount' => $shoppingcart->due_now,
				'currency' => 'IDR',
				'rate' => 1,
				'rate_from' => NULL,
				'rate_to' => NULL,
				'payment_status' => 0,
			);

			$shoppingcart->payment = $ShoppingcartPayment;
			Cache::forget('_'. $sessionId);
			Cache::add('_'. $sessionId, $shoppingcart, 172800);

			
			$response = Cache::get('_'. $sessionId);
		}
		return $response;
	}


	public static function remove_promocode($sessionId)
	{
		$contents = BokunHelper::get_removepromocode($sessionId);
        self::get_shoppingcart($sessionId,"update",$contents);
        return $contents;
	}

	public static function remove_activity($sessionId,$bookingId)
	{
		$contents = BokunHelper::get_removeactivity($sessionId,$bookingId);
		self::get_shoppingcart($sessionId,"update",$contents);
		return $sessionId;
	}

	public static function apply_promocode($sessionId,$promocode)
	{

		$status = false;
		$contents = BokunHelper::get_applypromocode($sessionId,$promocode);
		
		if(!isset($contents->message))
		{
			$status = true;
			self::get_shoppingcart($sessionId,"update",$contents);
		}
		return $status;
	}

	public static function paypal_rate($shoppingcart)
	{
		$amount = 'Total : '. self::convert_currency($shoppingcart->due_now,$shoppingcart->currency,env("PAYPAL_CURRENCY")) .' '. env("PAYPAL_CURRENCY");
		$value = $amount .'<br />Paypal Rate : 1 '. env("PAYPAL_CURRENCY") .' = '. self::convert_currency(1,env("PAYPAL_CURRENCY"),$shoppingcart->currency) .' '. $shoppingcart->currency;
		return $value;
	}

	public static function get_rate($shoppingcart)
	{
		$amount = $shoppingcart->shoppingcart_payment->rate;
		$value = '1 '. $shoppingcart->shoppingcart_payment->rate_to .' = '. $amount .' '. $shoppingcart->shoppingcart_payment->rate_from;
		return $value;
	}

	public static function get_bankcode($bank)
	{
		$bank = strtolower($bank);
		switch($bank)
		{
			case "permata":
				$bank_code = "013";
			break;
			default:
				$bank_code = '009';
		}
		return $bank_code;
	}

	public static function convert_currency($amount,$from,$to)
	{

		$rate = BokunHelper::get_currency();
		$oneusd = 1 / $rate;
		$paypal_charge = $oneusd * 4.4 / 100;
		$paypal_rate = $oneusd * 3.74 / 100;
		$rate = $oneusd - $paypal_rate - $paypal_charge;

		if($from!=$to)
		{
			if($from==env("BOKUN_CURRENCY"))
			{
				$value = ($amount / $rate);
			}

			if($to==env("BOKUN_CURRENCY"))
			{
				$value = ($amount * $rate);
			}
		}
		else
		{
			$value = $amount;
		}

		$value = number_format((float)$value, 2, '.', '');	
		return $value;
	}
	
	public static function get_ticket(){
    	$uuid = "VER-". rand(100000,999999);
    	while( Shoppingcart::where('confirmation_code','=',$uuid)->first() ){
        	$uuid = "VER-". rand(100000,999999);
    	}
    	return $uuid;
	}
	
	public static function payment_status($paymentStatus)
	{
		switch($paymentStatus)
                    {
                        case 1:
                            $paymentStatus = "AUTHORIZED";
                        break;
                        case 2:
                            $paymentStatus = "CAPTURED";
                        break;
                        case 3:
                            $paymentStatus = "VOIDED";
                        break;
                        case 4:
                            $paymentStatus = "UNPAID";
                        break;
                        default:
                            $paymentStatus = "NOT AVAILABLE";
                    }
       return $paymentStatus;
	}

	public static function payment_status_public($paymentStatus)
	{
		switch($paymentStatus)
                    {
                        case 1:
                            $paymentStatus = '<span class="badge badge-success">PAID</span>';
                        break;
                        case 2:
                            $paymentStatus = '<span class="badge badge-success">PAID</span>';
                        break;
                        case 3:
                            $paymentStatus = '<span class="badge badge-danger">REFUNDED</span>';
                        break;
                        case 4:
                            $paymentStatus = '<span class="badge badge-warning">UNPAID</span>';
                        break;
                        default:
                            $paymentStatus = "NOT AVAILABLE";
                    }
       return $paymentStatus;
	}

	public static function get_answer($shoppingcart,$question_id)
	{
		$value = '';
		foreach($shoppingcart->questions as $question)
        {
            if($question->question_id==$question_id)
            {
            	$value = $question->answer;
            }
        }
        return $value;
	}

	public static function get_answer_contact($shoppingcart)
	{
		$object = new \stdClass();
   		$object->firstName = '';
   		$object->lastName = '';
   		$object->email = '';
   		$object->phoneNumber = '';

   		$questions = $shoppingcart->shoppingcart_questions()->where('type','mainContactDetails')->get();
   		foreach($questions as $question)
   		{
   			if($question->question_id=="firstName") $object->firstName = $question->answer;
   			if($question->question_id=="lastName") $object->lastName = $question->answer;
   			if($question->question_id=="email") $object->email = $question->answer;
   			if($question->question_id=="phoneNumber") $object->phoneNumber = $question->answer;
   		}

   		return $object;
	}

	public static function get_answer_product($shoppingcart,$booking_id)
	{
		$value = '';
		foreach($shoppingcart->shoppingcart_questions()->where('booking_id',$booking_id)->whereNotNull('label')->get() as $shoppingcart_question)
		{
			$value .= $shoppingcart_question->label .' : '. $shoppingcart_question->answer .'<br>';
		}
        return $value;
	}


	public static function display_invoice($shoppingcart)
	{
		$invoice = '';
		$invoice .= '<b><a class="text-decoration-none text-theme" href="'.url('/api').'/pdf/invoice/'. $shoppingcart->session_id .'/Invoice-'. $shoppingcart->confirmation_code .'.pdf" target="_blank">'. $shoppingcart->confirmation_code .'</a> - INVOICE</b> <br />';
		$invoice .= ' <b>Channel :</b> '.$shoppingcart->booking_channel.' <br />';

		$main_contact = self::get_answer_contact($shoppingcart);

		$first_name = $main_contact->firstName;
		$last_name = $main_contact->lastName;
		$email = $main_contact->email;
		$phone = $main_contact->phoneNumber;

		if($first_name!='' || $last_name!='') $invoice .= ' <b>Name :</b> '.$first_name.'  '. $last_name .'<br />';
		if($email!='') $invoice .= ' <b>Email :</b> '.$email.' <br />';
		if($phone!='') $invoice .= ' <b>Phone :</b> '.$phone.' <br />';

		$invoice .= ' <b>Status :</b> '. self::booking_status($shoppingcart);

		return $invoice;
	}

	public static function display_product_detail($shoppingcart)
	{
		$product = '';
		$access_ticket = self::access_ticket($shoppingcart);

		foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
		{
			if($access_ticket)
			{
				$product .= '<b><a target="_blank" class="text-decoration-none text-theme" href="'.url('/api').'/pdf/ticket/'. $shoppingcart->session_id .'/Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf" target="_blank">'. $shoppingcart_product->product_confirmation_code .'</a> - '.$shoppingcart_product->title.'</b> <br />';
			}
			else
			{
				$product .= '<b>'.$shoppingcart_product->title.'</b> <br />';
			}
			

			if($shoppingcart_product->rate!="") $product .= $shoppingcart_product->rate .' <br />';
			$product .= ProductHelper::datetotext($shoppingcart_product->date) .' <br />';

			foreach($shoppingcart_product->shoppingcart_product_details()->get() as $shoppingcart_product_detail)
			{
				if($shoppingcart_product_detail->type=="product" || $shoppingcart_product_detail->type=="extra")
				{
					if($shoppingcart_product_detail->unit_price == "Price per booking")
					{
						$product .= $shoppingcart_product_detail->qty .' '. $shoppingcart_product_detail->unit_price .' ('. $shoppingcart_product_detail->people .' Person)<br>';
					}
					else
					{
						$product .= $shoppingcart_product_detail->qty .' '. $shoppingcart_product_detail->unit_price .'<br>';
					}
                                
				}
				elseif($shoppingcart_product_detail->type=="pickup")
				{
					$product .= $shoppingcart_product_detail->title .'<br>';
				}
			}

			$product .= BookingHelper::get_answer_product($shoppingcart,$shoppingcart_product->booking_id);

			$product .= '<br>';

		}

		return $product;
	}

	public static function access_ticket($shoppingcart)
	{
		$access = false;
		if($shoppingcart->booking_status=="CONFIRMED")
		{
			$access = true;
			if(isset($shoppingcart->shoppingcart_payment->payment_status))
			{
				$payment_status = $shoppingcart->shoppingcart_payment->payment_status;
				if($payment_status == 3 || $payment_status == 4)
				{
					$access = false;
				}
			}
		}
		return $access;
	}

	public static function booking_status($shoppingcart)
	{
		$booking_status = '';

		if($shoppingcart->booking_status=='CONFIRMED')
		{
			$booking_status = '<span class="badge badge-success">CONFIRMED</span> <br />';
			if(isset($shoppingcart->shoppingcart_payment->payment_status))
			{
				if($shoppingcart->shoppingcart_payment->payment_status==4)
				{
					$booking_status = '<span class="badge badge-warning">UNPAID</span> <br />';
				}
			}
		}
		else
		{
			$booking_status = '<span class="badge badge-danger">CANCELED</span> <br />';
		}
		return $booking_status;
	}
}
?>
