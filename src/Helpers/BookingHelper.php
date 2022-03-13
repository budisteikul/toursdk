<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Http\Request;


use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\ImageHelper;
use budisteikul\toursdk\Helpers\ProductHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use budisteikul\toursdk\Helpers\MidtransHelper;
use budisteikul\toursdk\Helpers\OyHelper;
use budisteikul\toursdk\Helpers\DokuHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;

use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Models\ShoppingcartProductDetail;
use budisteikul\toursdk\Models\ShoppingcartQuestion;
use budisteikul\toursdk\Models\ShoppingcartQuestionOption;
use budisteikul\toursdk\Models\ShoppingcartPayment;

use budisteikul\toursdk\Mail\BookingConfirmedMail;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BookingHelper {
	
	public static function env_paypalCurrency()
    {
        return env("PAYPAL_CURRENCY");
    }

    public static function env_bokunCurrency()
    {
        return env("BOKUN_CURRENCY");
    }

    public static function env_appApiUrl()
    {
        return env("APP_API_URL");
    }

    public static function env_appUrl()
    {
        return env("APP_URL");
    }

    public static function env_appName()
    {
        return env("APP_NAME");
    }

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
						$new_currency = 'IDR';
						$new_price = self::convert_currency($lineitems[$j]['unitPrice'],$data['currency'],$new_currency);
						$new_discount = self::convert_currency($lineitems[$j]['discountedUnitPrice'],$data['currency'],$new_currency);

						$shoppingcart_product_detail = new ShoppingcartProductDetail();
						$shoppingcart_product_detail->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_product_detail->type = $type_product;
						$shoppingcart_product_detail->title = $data['activityBookings'][$i]['product']['title'];
						$shoppingcart_product_detail->people = $lineitems[$j]['people'];
						$shoppingcart_product_detail->qty = $lineitems[$j]['quantity'];
						$shoppingcart_product_detail->price = $new_price;
						$shoppingcart_product_detail->unit_price = $unitPrice;

						$subtotal = $new_price * $shoppingcart_product_detail->qty;
						$discount = $subtotal - ($new_discount * $shoppingcart_product_detail->qty);
						$total = $subtotal - $discount;

						$shoppingcart_product_detail->currency = $new_currency;
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
						$new_currency = 'IDR';
						$new_price = self::convert_currency($lineitems[$j]['total'],$data['currency'],$new_currency);
						$new_discount = self::convert_currency($lineitems[$j]['discountedUnitPrice'],$data['currency'],$new_currency);

						$shoppingcart_product_detail = new ShoppingcartProductDetail();
						$shoppingcart_product_detail->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_product_detail->type = $type_product;
						$shoppingcart_product_detail->title = 'Pick-up and drop-off services';
						$shoppingcart_product_detail->people = $lineitems[$j]['people'];
						$shoppingcart_product_detail->qty = 1;

						$shoppingcart_product_detail->price = $new_price;
						$shoppingcart_product_detail->unit_price = $unitPrice;

						//$subtotal = $lineitems[$j]['total'];
						$subtotal = $new_price * $shoppingcart_product_detail->qty;
						$discount = $subtotal - $new_discount;
						$total = $subtotal - $discount;

						$shoppingcart_product_detail->currency = $new_currency;
						$shoppingcart_product_detail->discount = $discount;
						$shoppingcart_product_detail->subtotal = $subtotal;
						$shoppingcart_product_detail->total = $total;
						$shoppingcart_product_detail->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
				}
				
				$new_currency = 'IDR';
				ShoppingcartProduct::where('id',$shoppingcart_product->id)->update([
					'currency'=>$new_currency,
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
			

			$shoppingcart->currency = 'IDR';
			$shoppingcart->subtotal = $grand_subtotal;
			$shoppingcart->discount = $grand_discount;
			$shoppingcart->total = $grand_total;
			$shoppingcart->due_now = $grand_total;
			$shoppingcart->save();


			$new_currency = 'IDR';
			$shoppingcart_payment = new ShoppingcartPayment();
			$shoppingcart_payment->payment_provider = 'none';
			$shoppingcart_payment->amount = $grand_total;
			$shoppingcart_payment->rate = self::convert_currency(1,$data['currency'],$new_currency);
			$shoppingcart_payment->rate_from = $data['currency'];
			$shoppingcart_payment->rate_to = $new_currency;
			$shoppingcart_payment->currency = $new_currency;
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
				'product_id' => $sp_product_id,
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
				'when_to_ask' => 'booking',
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
						'when_to_ask' => 'booking',
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

			// ActivityBookings question per booking
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
						'when_to_ask' => 'booking',
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

			// ActivityBookings question per participant
			if(isset($questions->checkoutOptions[$ii]->participants))
			{
				$activityBookingId = $questions->checkoutOptions[$ii]->activityBookingDetail->activityBookingId;
				$participant_number = 1;
				for($jj = 0; $jj < count($questions->checkoutOptions[$ii]->participants); $jj++)
				{
					$participantQuestions = $questions->checkoutOptions[$ii]->participants[$jj]->participantQuestions;
					$scq_type = 'activityBookings';
					$scq_participant_number = $participant_number;
					//$scq_booking_id = $participantQuestions->bookingId;
					$order = 1;

					foreach($participantQuestions->questions as $question)
					{
						$scq_question_id =  $question->questionId.'_'.$participant_number;
						$scq_label = $question->label;
						$scq_data_format = NULL;
						if(isset($question->dataFormat)) $scq_data_format = $question->dataFormat;
						$scq_data_type = $question->dataType;
						$scq_help = NULL;
						if(isset($question->help)) $scq_help = $question->help;
						$scq_required = $question->required;
						$scq_select_option = $question->selectFromOptions;
						$scq_select_multiple = $question->selectMultiple;

						$ShoppingcartQuestionOptions = array();
						if($question->selectFromOptions)
						{
							$order_option = 1;
							foreach($question->answerOptions as $answerOption)
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
							'when_to_ask' => 'participant',
							'participant_number' => $scq_participant_number,
							'question_id' => $scq_question_id,
							'booking_id' => $activityBookingId,
							'label' => $scq_label,
							'help' => $scq_help,
							'data_type' => $scq_data_type,
							'data_format' => $scq_data_format,
							'required' => $scq_required,
							'select_option' => $scq_select_option,
							'select_multiple' => $scq_select_multiple,
							'order' => $order,
							'answer' => '',
							'question_options' => $ShoppingcartQuestionOptions
						);
						$order += 1;
					}

					$participant_number += 1;
					
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
				'product_id' => $sp_product_id,
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
						'when_to_ask' => 'booking',
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

			// ActivityBookings question per booking
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
						'when_to_ask' => 'booking',
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

			// ActivityBookings question per participant
			if(isset($questions->checkoutOptions[$ii]->participants))
			{
				$activityBookingId = $questions->checkoutOptions[$ii]->activityBookingDetail->activityBookingId;
				$participant_number = 1;
				for($jj = 0; $jj < count($questions->checkoutOptions[$ii]->participants); $jj++)
				{
					$participantQuestions = $questions->checkoutOptions[$ii]->participants[$jj]->participantQuestions;
					$scq_type = 'activityBookings';
					$scq_participant_number = $participant_number;
					//$scq_booking_id = $participantQuestions->bookingId;
					$order = 1;

					foreach($participantQuestions->questions as $question)
					{
						$scq_question_id =  $question->questionId.'_'.$participant_number;
						$scq_label = $question->label;
						$scq_data_format = NULL;
						if(isset($question->dataFormat)) $scq_data_format = $question->dataFormat;
						$scq_data_type = $question->dataType;
						$scq_help = NULL;
						if(isset($question->help)) $scq_help = $question->help;
						$scq_required = $question->required;
						$scq_select_option = $question->selectFromOptions;
						$scq_select_multiple = $question->selectMultiple;

						$ShoppingcartQuestionOptions = array();
						if($question->selectFromOptions)
						{
							$order_option = 1;
							foreach($question->answerOptions as $answerOption)
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
							'when_to_ask' => 'participant',
							'participant_number' => $scq_participant_number,
							'question_id' => $scq_question_id,
							'booking_id' => $activityBookingId,
							'label' => $scq_label,
							'help' => $scq_help,
							'data_type' => $scq_data_type,
							'data_format' => $scq_data_format,
							'required' => $scq_required,
							'select_option' => $scq_select_option,
							'select_multiple' => $scq_select_multiple,
							'order' => $order,
							'answer' => '',
							'question_options' => $ShoppingcartQuestionOptions
						);
						$order += 1;
					}

					$participant_number += 1;
					
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

            	

            	//if($status)
				//{
					if($question->question_id=="firstName")
						{
							$rules = array('firstName' => 'regex:/^[\pL\s]+$/u');

							$inputs = array(
    							'firstName' => $data['questions'][$question->question_id]
							);
							$validator = Validator::make($inputs, $rules);
							if($validator->fails()) {
    							$status = false;
								$array[$question->question_id] = array('Please use alphabetic characters only');
							}
						}

					if($question->question_id=="lastName")
						{
							$rules = array('lastName' => 'regex:/^[\pL\s]+$/u');

							$inputs = array(
    							'lastName' => $data['questions'][$question->question_id]
							);
							$validator = Validator::make($inputs, $rules);
							if($validator->fails()) {
    							$status = false;
								$array[$question->question_id] = array('Please use alphabetic characters only');
							}
						}

					if($question->question_id=="email")
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

				//}
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
                		if(isset($data['questions'][$question->question_id]))
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

	public static function get_firstAvailability($activityId,$year,$month)
	{
		
		$availability = self::get_calendar($activityId,$year,$month);
		$count_availability = count($availability->firstAvailableDay->availabilities);
		
		$dateObj = $availability->firstAvailableDay->dateObj;
		$month = date("n",$dateObj/1000);
		$year = date("Y",$dateObj/1000);
		$day = date("d",$dateObj/1000);
		$localizedDate = GeneralHelper::dateFormat($year.'-'.$month.'-'.$day,11);

		for($i=0;$i<$count_availability;$i++)
		{
			$value[] = $availability->firstAvailableDay->availabilities[$i]->activityAvailability;
		}
		
		$dataObj[] = [
			'date' => $dateObj,
			'localizedDate' => $localizedDate,
			'availabilities' => $value
		];
		return $dataObj;
	}

	public static function get_calendar($activityId,$year,$month)
	{
		$bookings = array();
		
		$group_shoppingcart_products = ShoppingcartProduct::with('shoppingcart')
		->WhereHas('shoppingcart', function($query) {
              $query->where('booking_status','CONFIRMED');
            })
		->where('product_id',$activityId)->whereYear('date','=',$year)->whereMonth('date','=',$month)->whereDate('date', '>=', Carbon::now())->groupBy(['date'])->select('date')->get();

		foreach($group_shoppingcart_products as $group_shoppingcart_product)
        {
        	$date = Carbon::parse($group_shoppingcart_product->date)->format('Y-m-d');
            $people = ShoppingcartProductDetail::with('shoppingcart_product')
            ->WhereHas('shoppingcart_product', function($query) use ($date,$activityId) {
              $query->whereDate('date','=',$date)->where(['product_id'=>$activityId]);
            })->get()->sum('people');

            $bookings[] = (object)[
            	"date" => $date,
            	"people" => $people,
        	];
        }

		
        $contents = BokunHelper::get_calendar($activityId,$year,$month);

        $value[] = $contents->firstAvailableDay;
        
        if(count($bookings)>0)
        {
        	foreach($value as $firstDay)
        	{
        		foreach($bookings as $booking)
				{
					if($booking->date == $firstDay->fullDate)
					{
						foreach($firstDay->availabilities as $availability)
                        {
                        	$availability->data->bookedParticipants +=  $booking->people;
							$availability->data->availabilityCount -= $booking->people;

							$availability->activityAvailability->bookedParticipants +=  $booking->people;
							$availability->activityAvailability->availabilityCount -= $booking->people;
                                        
							$availability->availabilityCount -= $booking->people;

							if($availability->availabilityCount<=0) $firstDay->soldOut = true;
                        }
					}
				}
        	}
        }

        

        if(count($bookings)>0)
        {
        foreach($contents->weeks as $week)
        {
            foreach($week->days as $day)
            {
                if(!$day->notInCurrentMonth)
                {
                    if(!$day->past)
                    {
                        if(!$day->pastCutoff)
                        {
                            if(!$day->soldOut)
                            {
                                foreach($bookings as $booking)
                                {
                                    if($booking->date == $day->fullDate)
                                    {
                                        foreach($day->availabilities as $availability)
                                        {
                                            $availability->data->bookedParticipants +=  $booking->people;
                                            $availability->data->availabilityCount -= $booking->people;

                                            $availability->activityAvailability->bookedParticipants +=  $booking->people;
                                            $availability->activityAvailability->availabilityCount -= $booking->people;
                                        
                                            $availability->availabilityCount -= $booking->people;

                                            if($availability->availabilityCount<=0) $day->soldOut = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
            }
        }
    	}

        return $contents;
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
			if(isset($question->when_to_ask)) $shoppingcart_question->when_to_ask = $question->when_to_ask;
			if(isset($question->participant_number)) $shoppingcart_question->participant_number = $question->participant_number;
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

		if(isset($shoppingcart_json->payment->payment_provider)) $shoppingcart_payment->payment_provider = $shoppingcart_json->payment->payment_provider;
		if(isset($shoppingcart_json->payment->payment_type)) $shoppingcart_payment->payment_type = $shoppingcart_json->payment->payment_type;
		if(isset($shoppingcart_json->payment->bank_name)) $shoppingcart_payment->bank_name = $shoppingcart_json->payment->bank_name;
		if(isset($shoppingcart_json->payment->bank_code)) $shoppingcart_payment->bank_code = $shoppingcart_json->payment->bank_code;
		if(isset($shoppingcart_json->payment->va_number)) $shoppingcart_payment->va_number = $shoppingcart_json->payment->va_number;

		if(isset($shoppingcart_json->payment->snaptoken)) $shoppingcart_payment->snaptoken = $shoppingcart_json->payment->snaptoken;
		if(isset($shoppingcart_json->payment->qrcode)) $shoppingcart_payment->qrcode = $shoppingcart_json->payment->qrcode;
		if(isset($shoppingcart_json->payment->link)) $shoppingcart_payment->link = $shoppingcart_json->payment->link;
		if(isset($shoppingcart_json->payment->redirect)) $shoppingcart_payment->redirect = $shoppingcart_json->payment->redirect;

		if(isset($shoppingcart_json->payment->order_id)) $shoppingcart_payment->order_id = $shoppingcart_json->payment->order_id;
		if(isset($shoppingcart_json->payment->authorization_id)) $shoppingcart_payment->authorization_id = $shoppingcart_json->payment->authorization_id;
		if(isset($shoppingcart_json->payment->amount)) $shoppingcart_payment->amount = $shoppingcart_json->payment->amount;
		if(isset($shoppingcart_json->payment->currency)) $shoppingcart_payment->currency = $shoppingcart_json->payment->currency;
		if(isset($shoppingcart_json->payment->rate)) $shoppingcart_payment->rate = $shoppingcart_json->payment->rate;
		if(isset($shoppingcart_json->payment->rate_from)) $shoppingcart_payment->rate_from = $shoppingcart_json->payment->rate_from;
		if(isset($shoppingcart_json->payment->rate_to)) $shoppingcart_payment->rate_to = $shoppingcart_json->payment->rate_to;
		if(isset($shoppingcart_json->payment->expiration_date)) $shoppingcart_payment->expiration_date = $shoppingcart_json->payment->expiration_date;
		if(isset($shoppingcart_json->payment->payment_status)) $shoppingcart_payment->payment_status = $shoppingcart_json->payment->payment_status;
		
		$shoppingcart_payment->save();

		
		
		return $shoppingcart;
		
	}

	public static function set_bookingStatus($sessionId,$booking_status='CONFIRMED')
	{
		$shoppingcart = Cache::get('_'. $sessionId);
        $shoppingcart->booking_status = $booking_status;
        Cache::forget('_'. $sessionId);
        Cache::add('_'. $sessionId, $shoppingcart, 172800);
        return $shoppingcart;
	}

	public static function set_confirmationCode($sessionId)
	{
		$shoppingcart = Cache::get('_'. $sessionId);
		$confirmation_code = self::get_ticket();
		$shoppingcart->confirmation_code = $confirmation_code;
        Cache::forget('_'. $sessionId);
        Cache::add('_'. $sessionId, $shoppingcart, 172800);
        return $shoppingcart;
	}

	public static function confirm_booking($sessionId,$sendMail=true)
	{
		$shoppingcart = Cache::get('_'. $sessionId);

        $shoppingcart = self::confirm_transaction($sessionId);

        self::shoppingcart_clear($sessionId);

        if($sendMail)
        {
        	self::shoppingcart_mail($shoppingcart);
        }
        return $shoppingcart;
	}

	public static function confirm_payment($shoppingcart,$status,$force=false)
	{
		if($status=="CONFIRMED")
		{
			if($force)
			{
				$shoppingcart->booking_status = "PENDING";
				$shoppingcart->save();
			}

			if($shoppingcart->booking_status=="PENDING")
			{
				$shoppingcart->booking_status = 'CONFIRMED';
				$shoppingcart->save();
				$shoppingcart->shoppingcart_payment->payment_status = 2;
				$shoppingcart->shoppingcart_payment->save();
			}
		}

		if($status=="PENDING")
		{
			if($force)
			{
				$shoppingcart->booking_status = "PENDING";
				$shoppingcart->save();
			}

			if($shoppingcart->booking_status!="PENDING")
			{
				$shoppingcart->booking_status = 'PENDING';
				$shoppingcart->save();
				$shoppingcart->shoppingcart_payment->payment_status = 4;
				$shoppingcart->shoppingcart_payment->save();
			}
		}

		if($status=="CANCELED")
		{
			if($force)
			{
				$shoppingcart->booking_status = "PENDING";
				$shoppingcart->save();
			}

			if($shoppingcart->booking_status=="PENDING")
			{

				$shoppingcart->booking_status = 'CANCELED';
				$shoppingcart->save();
				$shoppingcart->shoppingcart_payment->payment_status = 3;
				$shoppingcart->shoppingcart_payment->save();
			}
		}

		return $shoppingcart;
		
	}

	public static function booking_expired($shoppingcart)
	{
		if($shoppingcart->booking_status=="PENDING")
        {
            $due_date = self::due_date($shoppingcart,"database");
            if(Carbon::parse($due_date)->isPast())
            {
                self::confirm_payment($shoppingcart,"CANCELED");
            }
        }
	}

	public static function due_date($shoppingcart, $data_type = "json")
	{
		$due_date = null;
		$date = null;

		if($data_type=="json")
		{
			if(isset($shoppingcart->payment->expiration_date)) $date = $shoppingcart->payment->expiration_date;
		}
		else
		{
			$date = $shoppingcart->shoppingcart_payment->expiration_date;
		}

		if($date!==null)
		{
			$due_date = $date;
		}
		else
		{
			$date_arr = array();

			if($data_type=="json")
			{
        		foreach($shoppingcart->products as $product)
        		{
            		$date_arr[] = $product->date;
        		}
			}
			else
			{
				foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
				{
				$date_arr[] = $shoppingcart_product->date;
            	}
			}

			usort($date_arr, function($a, $b) {
            	$dateTimestamp1 = strtotime($a);
            	$dateTimestamp2 = strtotime($b);
            	return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
        	});

        	$due_date = $date_arr[0];
		}
		

		return $due_date;
	}

	

	public static function create_payment($sessionId,$payment_provider="none",$bank="")
	{
		$shoppingcart = Cache::get('_'. $sessionId);

		$first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $email = BookingHelper::get_answer($shoppingcart,'email');
        $phone = BookingHelper::get_answer($shoppingcart,'phoneNumber');

        $contact = new \stdClass();
        $contact->first_name = $first_name;
        $contact->last_name = $last_name;
        $contact->name = $first_name .' '. $last_name;
        $contact->email = $email;
        $contact->phone = $phone;


        $due_date = self::due_date($shoppingcart);

        

        $date1 = Carbon::now();
        $date2 = Carbon::parse($due_date);
        $mins_expired  = $date2->diffInMinutes($date1, true);
        $date_expired = Carbon::parse($due_date)->formatLocalized('%Y-%m-%d %H:%M:%S');
        $date_now = Carbon::parse($date1)->formatLocalized('%Y-%m-%d %H:%M:%S +0700');

        $response = NULL;
        $payment_type = NULL;
		$bank_name = NULL;
		$bank_code = NULL;
		$va_number = NULL;
		$snaptoken = NULL;
		$qrcode = NULL;
		$link = NULL;
		$redirect = NULL;
		$order_id = NULL;
		$authorization_id = NULL;
		$amount = NULL;
		$currency = NULL;
		$rate = NULL;
		$rate_from = NULL;
		$rate_to = NULL;
		$expiration_date = NULL;
		$payment_status = NULL;

		$transaction = new \stdClass();
        $transaction->id = $shoppingcart->confirmation_code;
        $transaction->amount = $shoppingcart->due_now;
        $transaction->payment_provider = $payment_provider;
        $transaction->bank = $bank;
        $transaction->mins_expired = $mins_expired;
        $transaction->date_expired = $date_expired;
        $transaction->date_now = $date_now;
        $transaction->finish_url = '/booking/receipt/'. $sessionId .'/'. $shoppingcart->confirmation_code;

        $data = new \stdClass();
        $data->contact = $contact;
        $data->transaction = $transaction;

        

		switch($payment_provider)
		{
			case "oyindonesia":
				$amount = $shoppingcart->due_now;
				$currency = 'IDR';
				$rate = 1;
				$payment_status = 4;
				$response = OyHelper::createPayment($data);
			break;
			case "doku":
				$amount = $shoppingcart->due_now;
				$currency = 'IDR';
				$rate = 1;
				$payment_status = 4;
				$response = DokuHelper::createPayment($data);
			break;
			case "midtrans":
				$amount = $shoppingcart->due_now;
				$currency = 'IDR';
				$rate = 1;
				$payment_status = 4;
				$response = MidtransHelper::createPayment($data);
			break;
			case "paypal":
				$payment_provider = 'paypal';
				$amount = self::convert_currency($shoppingcart->due_now,$shoppingcart->currency,self::env_paypalCurrency(),"PAYPAL");
				$currency = self::env_paypalCurrency();
				$rate = self::convert_currency(1,self::env_paypalCurrency(),$shoppingcart->currency,"PAYPAL");
				$rate_from = $shoppingcart->currency;
				$rate_to = self::env_paypalCurrency();

				$data->transaction->amount = $amount;
				$data->transaction->currency = $currency;

				$payment_status = 0;

				$response = PaypalHelper::createPayment($data);
			break;
			default:
				$payment_provider = 'none';
				$amount = $shoppingcart->due_now;
				$currency = 'IDR';
				$rate = 1;
				$payment_status = 0;
		}

		if(isset($response->payment_type)) $payment_type = $response->payment_type;
		if(isset($response->bank_name)) $bank_name = $response->bank_name;
		if(isset($response->bank_code)) $bank_code = $response->bank_code;
		if(isset($response->va_number)) $va_number = $response->va_number;
		if(isset($response->snaptoken)) $snaptoken = $response->snaptoken;
		if(isset($response->qrcode)) $qrcode = $response->qrcode;
		if(isset($response->link)) $link = $response->link;
		if(isset($response->redirect)) $redirect = $response->redirect;
		if(isset($response->expiration_date)) $expiration_date = $response->expiration_date;

		$ShoppingcartPayment = (object) array(
			'payment_provider' => $payment_provider,
			'payment_type' => $payment_type,
			'bank_name' => $bank_name,
			'bank_code' => $bank_code,
			'va_number' => $va_number,
			'snaptoken' => $snaptoken,
			'qrcode' => $qrcode,
			'link' => $link,
			'redirect' => $redirect,
			'order_id' => $order_id,
			'authorization_id' => $authorization_id,
			'amount' => $amount,
			'currency' => $currency,
			'rate' => $rate,
			'rate_from' => $rate_from,
			'rate_to' => $rate_to,
			'expiration_date' => $expiration_date,
			'payment_status' => $payment_status,
		);

		$shoppingcart->payment = $ShoppingcartPayment;
		Cache::forget('_'. $sessionId);
		Cache::add('_'. $sessionId, $shoppingcart, 172800);

		return $response;
	}

	public static function delete_shoppingcart($shoppingcart)
	{
		$shoppingcart->delete();
		FirebaseHelper::delete($shoppingcart,'receipt');
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
		$amount = 'Total : '. self::convert_currency($shoppingcart->due_now,$shoppingcart->currency,self::env_paypalCurrency(),"PAYPAL") .' '. self::env_paypalCurrency();
		$value = $amount .'<br />Paypal Rate : 1 '. self::env_paypalCurrency() .' = '. self::convert_currency(1,self::env_paypalCurrency(),$shoppingcart->currency,"PAYPAL") .' '. $shoppingcart->currency;
		return $value;
	}

	public static function get_rate($shoppingcart)
	{
		$amount = $shoppingcart->shoppingcart_payment->rate;
		$value = '1 '. $shoppingcart->shoppingcart_payment->rate_to .' = '. $amount .' '. $shoppingcart->shoppingcart_payment->rate_from;
		return $value;
	}

	public static function convert_currency($amount,$from,$to,$booking_fee="")
	{

		$rate = BokunHelper::get_currency();

		$oneusd = 1 / $rate;
		if($booking_fee=="PAYPAL")
		{
			$paypal_charge = $oneusd * 4.4 / 100;
			$paypal_rate = $oneusd * 3.74 / 100;
			$rate = $oneusd - $paypal_rate - $paypal_charge;
		}
		else
		{
			$rate = $oneusd;
		}
		

		if($from!=$to)
		{
			if($from==self::env_bokunCurrency())
			{
				$value = ($amount / $rate);
			}

			if($to==self::env_bokunCurrency())
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
    	$uuid = "VER-". date('YmdHi') . rand(10,99);
    	while( Shoppingcart::where('confirmation_code','=',$uuid)->first() ){
        	$uuid = "VER-". date('YmdHi') . rand(10,99);
    	}
    	return $uuid;
	}
	
	public static function get_bookingStatus($shoppingcart)
	{
		$value = '';
		if($shoppingcart->booking_status=="CONFIRMED")
		{
			$value = '<span class="badge badge-success" style="font-size: 20px;">CONFIRMED</span>';
		}
		else if($shoppingcart->booking_status=="PENDING")
		{
			$value = '<span class="badge badge-info" style="font-size: 20px;">PENDING</span>';
		}
		else
		{
			$value = '<span class="badge badge-danger" style="font-size: 20px;">CANCELED</span>';
		}
		return $value;
	}

	public static function have_payment($shoppingcart)
	{
		$status = false;
		if(isset($shoppingcart->shoppingcart_payment))
		{
			$status = true;
		}
		return $status;
	}

	public static function get_paymentStatus($shoppingcart)
	{
		if(self::have_payment($shoppingcart))
		{
			if($shoppingcart->shoppingcart_payment->payment_provider=="paypal")
            {
            	$text = '';

            	$text .= 'Total : '.$shoppingcart->shoppingcart_payment->currency.' '. $shoppingcart->shoppingcart_payment->amount .'<br />';
				$text .= 'Paypal Rate : '. BookingHelper::get_rate($shoppingcart) .'<br />';
            	

            	switch($shoppingcart->shoppingcart_payment->payment_status)
				{
					case 1:
						return '
								<div class="card mb-4">
								<span class="badge badge-success invoice-color-success" style="font-size:20px;"><i class="fab fa-paypal"></i> PAYPAL AUTHORIZED</span>
								<div class="card-body bg-light">
								'. $text .'
								</div>
								</div>';
					break;
					case 2:
						return '
								<div class="card mb-4">
								<span class="badge badge-success invoice-color-success" style="font-size:20px;"><i class="fab fa-paypal"></i> PAYPAL PAID</span>
								<div class="card-body bg-light">
								'. $text .'
								</div>
								</div>';
					break;
					case 3:
						return '
								<div class="card mb-4">
								<span class="badge badge-danger invoice-color-danger" style="font-size:20px;"><i class="fab fa-paypal"></i> PAYPAL VOIDED</span>
								<div class="card-body bg-light">
								'. $text .'
								</div>
								</div>';
					break;
					default:
						return '';
				}
            }
            if($shoppingcart->shoppingcart_payment->payment_type=="bank_transfer")
            {
            	$main_contact = self::get_answer_contact($shoppingcart);
            	switch($shoppingcart->shoppingcart_payment->payment_status)
				{
					case 2:
						return '<div class="card mb-4">
								<span class="badge badge-success invoice-color-success" style="font-size:20px;">
								<i class="fas fa-university"></i> BANK TRANSFER PAID </span>
								</div>';
						break;
					case 3:
						return '<div class="card mb-4">
								<span class="badge badge-danger invoice-color-danger" style="font-size:20px;">
								<i class="fas fa-university"></i> BANK TRANSFER UNPAID </span>
								</div>';
						break;	
					case 4:
						return '
								<div class="card mb-1">
								<span class="badge badge-info invoice-color-info" style="font-size:20px;">
								<i class="fas fa-university"></i> WAITING FOR PAYMENT </span>
								</div>
								<div class="card mb-4">
								<input type="hidden" id="va_number" value="'. $shoppingcart->shoppingcart_payment->va_number .'">
								<input type="hidden" id="va_total" value="'. $shoppingcart->shoppingcart_payment->amount .'">
								<div class="card-body bg-light">

								<div>Bank Name : </div>
								<div class="mb-2"><b>'. Str::upper($shoppingcart->shoppingcart_payment->bank_name) .' ('. $shoppingcart->shoppingcart_payment->bank_code .')</b></div>
								<div>Virtual Account Number : </div>
								<div class="mb-2"><b id="va">'. GeneralHelper::splitSpace($shoppingcart->shoppingcart_payment->va_number,4,0) .'</b> 
								<button id="va_number_button" onclick="copyToClipboard(\'#va_number\')" title="Copied" data-toggle="tooltip" data-placement="right" data-trigger="click" class="btn btn-light btn-sm invoice-hilang"><i class="far fa-copy"></i></button>
								
								 </div>
								<div>Total Bill : </div>
								<div class="mb-2"><b>'. GeneralHelper::formatRupiah($shoppingcart->shoppingcart_payment->amount) .'</b> <button onclick="copyToClipboard(\'#va_total\')" id="va_total_button" data-toggle="tooltip" data-placement="right" title="Copied" data-trigger="click" class="btn btn-light btn-sm invoice-hilang"><i class="far fa-copy"></i></button></div>

								
								</div>
								</div>
								';
						break;
					default:
						return '';
				}
            }
            if($shoppingcart->shoppingcart_payment->payment_type=="qris")
            {

            	switch($shoppingcart->shoppingcart_payment->payment_status)
				{
					case 2:
						return '<div class="card mb-4">
								<span class="badge badge-success invoice-color-success" style="font-size:20px;">
								<i class="fas fa-qrcode"></i> QRIS PAID </span>
								</div>';
						break;
					case 3:
						return '<div class="card mb-4">
								<span class="badge badge-danger invoice-color-danger" style="font-size:20px;">
								<i class="fas fa-qrcode"></i> QRIS UNPAID </span>
								</div>';
						break;
					case 4:

						$merchant_name = self::env_appName();
						$nmid = '';
						
						if($shoppingcart->shoppingcart_payment->bank_name=="shopeepay")
						{
							$nmid = 'ID1022150910159';
						}
						if($shoppingcart->shoppingcart_payment->bank_name=="gopay")
						{
							$nmid = 'ID1022148923652';
						}
						

						return '
								
								<div class="card mb-1">
								<span class="badge badge-info invoice-color-info" style="font-size:20px;">
								<i class="fas fa-qrcode"></i> WAITING FOR PAYMENT </span>
								</div>
								<div class="card mb-1 img-fluid invoice-hilang"  style="min-height:300px; max-width:505px;">
								
								<img class="card-img-top" src="'. url('/img/qris-template.jpg') .'" alt="Card image" style="width:100%">
								
								
								<div class="card-img-overlay">
									<div class="row h-100">
   										<div class="col-sm-12 my-auto text-center">
     										
    										<h1 class="mb-2 mt-4">'. $merchant_name .'</h1>
    								
    										<h5>NMID : '.$nmid.'</h5>
    									
    										<img id="qris-img" class="img-fluid border border-white" alt="QRIS" style="max-width:250px;" src="'. $shoppingcart->shoppingcart_payment->qrcode .'">
    										
    										

   										</div>
									</div>
									
  								</div>
								
								</div>
								<div class="card mb-4">
								<a href="'. self::env_appApiUrl() .'/qrcode/'.$shoppingcart->session_id.'/'. $shoppingcart->confirmation_code .'" type="button" class="invoice-hilang btn btn-success invoice-hilang ">or Download QRCODE <i class="fas fa-download"></i> </a>
								</div>
								';
						break;
					default:
						return '';
				}
            }
            if($shoppingcart->shoppingcart_payment->payment_type=="ewallet")
            {

            	switch($shoppingcart->shoppingcart_payment->payment_status)
				{
					case 2:
						return '<div class="card mb-4">
								<span class="badge badge-success invoice-color-success" style="font-size:20px;">
								<i class="fas fa-wallet"></i> E-WALLET PAID </span>
								</div>';
						break;
					case 3:
						return '<div class="card mb-4">
								<span class="badge badge-danger invoice-color-danger" style="font-size:20px;">
								<i class="fas fa-wallet"></i> E-WALLET UNPAID </span>
								</div>';
						break;
					case 4:
						if($shoppingcart->shoppingcart_payment->bank_name=="gopay")
						{
							$button = '<a class="btn btn-outline-secondary w-100" href="'. url('/api/redirect/'. $shoppingcart->session_id .'/'. $shoppingcart->confirmation_code) .'"><b class="invoice-hilang"> Open <img height="30" src="'. url('/img/ewallet/gopay.png') .'" /> App</b></a>';
						}
						
						return '
								<div class="card mb-1">
								<span class="badge badge-info invoice-color-info" style="font-size:20px;">
								<i class="fas fa-wallet"></i> WAITING FOR PAYMENT </span>
								</div>
								<div class="card mb-4">
								
								<div class="card-body bg-light">

									'.$button.'
								
								</div>
								</div>
								';
						break;
					default:
						return '';
				}
            }
            if($shoppingcart->shoppingcart_payment->payment_provider=="none")
            {
            	switch($shoppingcart->shoppingcart_payment->payment_status)
				{
					case 3:
						return '<div class="card mb-4">
            				<span class="badge badge-danger invoice-color-danger" style="font-size:20px;">UNPAID</span>
							</div>';
					default:
						return '<div class="card mb-4">
            				<span class="badge badge-success invoice-color-success" style="font-size:20px;">PAID</span>
							</div>';
				}
            	
            }
		}
		return '';
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
		foreach($shoppingcart->shoppingcart_questions()->where('when_to_ask','booking')->where('booking_id',$booking_id)->whereNotNull('label')->get() as $shoppingcart_question)
		{
			$value .= $shoppingcart_question->label .' : '. $shoppingcart_question->answer .'<br>';
		}

		$participants = $shoppingcart->shoppingcart_questions()->where('when_to_ask','participant')->where('booking_id',$booking_id)->select('participant_number')->groupBy('participant_number')->get();
		
		foreach($participants as $participant)
		{
			$value .= '<b>Participant '. $participant->participant_number .'</b><br />';
			foreach($shoppingcart->shoppingcart_questions()->where('when_to_ask','participant')->where('booking_id',$booking_id)->where('participant_number',$participant->participant_number)->get() as $participant_detail)
			{
				$value .= ''.$participant_detail->label .' : <span class="text-muted">'. $participant_detail->answer .'</span><br>';
			}
		}
		
		if($value!="")
		{
			$value = '<div class="card mb-2 mt-4"><div class="card-body"><b>Note</b><br />'.$value .'</div></div>';
		}
		
        return $value;
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

	

	public static function create_manual_pdf($shoppingcart)
	{
        $pdf = PDF::setOptions(['tempDir' =>  storage_path(),'fontDir' => storage_path(),'fontCache' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.manual.manual', compact('shoppingcart'))->setPaper('a4', 'portrait');
        return $pdf;
	}

	public static function create_invoice_pdf($shoppingcart)
	{
		$qrcode = base64_encode(QrCode::errorCorrection('H')->format('png')->size(111)->margin(0)->generate( self::env_appUrl() .'/booking/receipt/'.$shoppingcart->session_id.'/'.$shoppingcart->confirmation_code  ));
        $pdf = PDF::setOptions(['tempDir' =>  storage_path(),'fontDir' => storage_path(),'fontCache' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.invoice', compact('shoppingcart','qrcode'))->setPaper('a4', 'portrait');
        return $pdf;
	}

	public static function create_instruction_pdf($shoppingcart)
	{
		$customPaper = array(0,0,430,2032);
		$pdf = PDF::setOptions(['tempDir' => storage_path(),'fontDir' => storage_path(),'fontCache' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.manual.bank_transfer', compact('shoppingcart'))->setPaper($customPaper,'portrait');
		return $pdf;
	}

	public static function create_ticket_pdf($shoppingcart_product)
	{
		$customPaper = array(0,0,300,540);
        $qrcode = base64_encode(QrCode::errorCorrection('H')->format('png')->size(111)->margin(0)->generate( self::env_appUrl() .'/booking/receipt/'.$shoppingcart_product->shoppingcart->session_id.'/'.$shoppingcart_product->shoppingcart->confirmation_code  ));
        $pdf = PDF::setOptions(['tempDir' => storage_path(),'fontDir' => storage_path(),'fontCache' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.ticket', compact('shoppingcart_product','qrcode'))->setPaper($customPaper);
        return $pdf;
	}

}
?>
