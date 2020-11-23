<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Http\Request;

use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\ImageHelper;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Models\ShoppingcartRate;
use budisteikul\toursdk\Models\ShoppingcartQuestion;
use budisteikul\toursdk\Models\ShoppingcartQuestionOption;
use budisteikul\toursdk\Models\ShoppingcartPayment;
use Illuminate\Support\Facades\Mail;
use budisteikul\toursdk\Mail\BookingConfirmedMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

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
				$product = Product::where('bokun_id',$data['activityBookings'][$i]['productId'])->firstOrFail();

				$shoppingcart_product = new ShoppingcartProduct();
				$shoppingcart_product->shoppingcart_id = $shoppingcart->id;
				$shoppingcart_product->booking_id = $data['activityBookings'][$i]['bookingId'];
				$shoppingcart_product->product_confirmation_code = $data['activityBookings'][$i]['productConfirmationCode'];
				$shoppingcart_product->product_id = $data['activityBookings'][$i]['productId'];
				if(isset($data['activityBookings'][$i]['invoice']['product']['keyPhoto']['derived'][2]['url']))
				{
					$shoppingcart_product->image = $data['activityBookings'][$i]['invoice']['product']['keyPhoto']['derived'][2]['url'];
				}
				else
				{
					$shoppingcart_product->image = ImageHelper::thumbnail($product);
				}
				$shoppingcart_product->title = $data['activityBookings'][$i]['product']['title'];
				$shoppingcart_product->rate = $data['activityBookings'][$i]['rateTitle'];
				$shoppingcart_product->date = self::texttodate($data['activityBookings'][$i]['invoice']['dates']);
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
						$shoppingcart_rate = new ShoppingcartRate();
						$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_rate->type = $type_product;
						$shoppingcart_rate->title = $data['activityBookings'][$i]['product']['title'];
						$shoppingcart_rate->qty = $lineitems[$j]['quantity'];
						$shoppingcart_rate->price = $lineitems[$j]['unitPrice'];
						$shoppingcart_rate->unit_price = $unitPrice;
						$subtotal = $lineitems[$j]['unitPrice'] * $shoppingcart_rate->qty;
						$discount = $subtotal - ($lineitems[$j]['discountedUnitPrice'] * $shoppingcart_rate->qty);
						$total = $subtotal - $discount;
						$shoppingcart_rate->currency = $data['currency'];
						$shoppingcart_rate->discount = $discount;
						$shoppingcart_rate->subtotal = $subtotal;
						$shoppingcart_rate->total = $total;
						$shoppingcart_rate->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
					if($type_product=="pickup")
					{
						$shoppingcart_rate = new ShoppingcartRate();
						$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_rate->type = $type_product;
						$shoppingcart_rate->title = 'Pick-up and drop-off services';
						$shoppingcart_rate->qty = 1;
						$shoppingcart_rate->price = $lineitems[$j]['total'];
						$shoppingcart_rate->unit_price = $unitPrice;
						$subtotal = $lineitems[$j]['total'];
						$discount = $subtotal - $lineitems[$j]['discountedUnitPrice'];
						$total = $subtotal - $discount;
						$shoppingcart_rate->currency = $data['currency'];
						$shoppingcart_rate->discount = $discount;
						$shoppingcart_rate->subtotal = $subtotal;
						$shoppingcart_rate->total = $total;
						$shoppingcart_rate->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
				}
				
				ShoppingcartProduct::where('id',$shoppingcart_product->id)->update([
					'currency'=>$data['currency'],
					'subtotal'=>$subtotal_product,
					'discount'=>$total_discount,
					'total'=>$total_product
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
			$shoppingcart->save();

			//$shoppingcart_payment = new ShoppingcartPayment();
			//$shoppingcart_payment->amount = self::convert_currency($grand_total,$contents->customerInvoice->currency,'USD');
			//$shoppingcart_payment->payment_status = 0;
			//$shoppingcart_payment->shoppingcart_id = $shoppingcart->id;
			//$shoppingcart_payment->save();
			
			return $shoppingcart;
	}
	
	public static function insert_shoppingcart($contents,$id)
	{
		$shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$id)->delete();
		
		$activity = $contents->activityBookings;
		$shoppingcart = new Shoppingcart();
		$shoppingcart->session_id = $id;
		$shoppingcart->booking_channel = 'WEBSITE';
		$shoppingcart->confirmation_code = self::get_ticket();
		$shoppingcart->currency = $contents->customerInvoice->currency;
		if(isset($contents->promoCode)) $shoppingcart->promo_code = $contents->promoCode->code;
		$shoppingcart->save();
		


		$grand_total = 0;
		$grand_subtotal = 0;
		$grand_discount = 0;
		for($i=0;$i<count($activity);$i++)
		{

			$product = Product::where('bokun_id',$activity[$i]->activity->id)->firstOrFail();

			$product_invoice = $contents->customerInvoice->productInvoices;
			$lineitems = $product_invoice[$i]->lineItems;
			
			
			$shoppingcart_product = new ShoppingcartProduct();
			
			
			$shoppingcart_product->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_product->product_confirmation_code = $activity[$i]->productConfirmationCode;
			$shoppingcart_product->booking_id = $activity[$i]->id;
			$shoppingcart_product->product_id = $activity[$i]->activity->id;
			if(isset($product_invoice[$i]->product->keyPhoto->derived[2]->url))
			{
				$shoppingcart_product->image = $product_invoice[$i]->product->keyPhoto->derived[2]->url;
			}
			else
			{
				$shoppingcart_product->image = ImageHelper::thumbnail($product);
			}
					
			$shoppingcart_product->title = $activity[$i]->activity->title;
			$shoppingcart_product->rate = $activity[$i]->rate->title;
			$shoppingcart_product->currency = $contents->customerInvoice->currency;
			$shoppingcart_product->date = self::texttodate($product_invoice[$i]->dates);
			$shoppingcart_product->save();
			
			$subtotal_product = 0;
			$total_discount = 0;
			$total_product = 0;
			for($z=0;$z<count($lineitems);$z++)
			{
					$itemBookingId = $lineitems[$z]->itemBookingId;
					$itemBookingId = explode("_",$itemBookingId);
					
					$type_product = '';
					$unitPrice = 'Price per booking';
					
					if($activity[$i]->extrasPrice>0)
					{
						$check_extra = false;
						for($k=0;$k<count($activity[$i]->extraBookings);$k++)
						{
							if($itemBookingId[1]==$activity[$i]->extraBookings[$k]->id)
							{
								$check_extra = true;
							}
							
						}
						if(!$check_extra)
						{
							if($itemBookingId[1]!="pickup")
							{
								$type_product = 'product';
								if($lineitems[$z]->title!="Passengers")
								{
									$unitPrice = $lineitems[$z]->title;
								}
							}
						}
					}
					else
					{
						if($itemBookingId[1]!="pickup")
						{
							$type_product = 'product';
							if($lineitems[$z]->title!="Passengers")
							{
								$unitPrice = $lineitems[$z]->title;
							}
						}
					}
					
					if($itemBookingId[1]=="pickup"){
						$type_product = "pickup";
					}
					
					
					
					if($type_product=="product")
					{
						
						$shoppingcart_rate = new ShoppingcartRate();
						
						$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_rate->type = $type_product;
						$shoppingcart_rate->title = $activity[$i]->activity->title;
						$shoppingcart_rate->qty = $lineitems[$z]->quantity;
						$shoppingcart_rate->price = $lineitems[$z]->unitPrice;
						$shoppingcart_rate->unit_price = $unitPrice;
						$subtotal = $lineitems[$z]->unitPrice * $shoppingcart_rate->qty;
						$discount = $subtotal - ($lineitems[$z]->discountedUnitPrice * $shoppingcart_rate->qty);
						$total = $subtotal - $discount;
						$shoppingcart_rate->discount = $discount;
						$shoppingcart_rate->subtotal = $subtotal;
						$shoppingcart_rate->currency = $contents->customerInvoice->currency;
						$shoppingcart_rate->total = $total;
						$shoppingcart_rate->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
					if($type_product=="pickup")
					{
						$shoppingcart_rate = new ShoppingcartRate();
						$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_rate->type = $type_product;
						$shoppingcart_rate->title = 'Pick-up and drop-off services';
						$shoppingcart_rate->qty = 1;
						$shoppingcart_rate->price = $lineitems[$z]->total;
						$shoppingcart_rate->unit_price = $unitPrice;
						$subtotal = $lineitems[$z]->total;
						$discount = $subtotal - $lineitems[$z]->discountedUnitPrice;
						$total = $subtotal - $discount;
						$shoppingcart_rate->discount = $discount;
						$shoppingcart_rate->subtotal = $subtotal;
						$shoppingcart_rate->total = $total;
						$shoppingcart_rate->currency = $contents->customerInvoice->currency;
						$shoppingcart_rate->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}	
					
						if(isset($activity[$i]->pickupPlace->title))
						{
							$shoppingcart_question = new ShoppingcartQuestion();
							$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
							$shoppingcart_question->type = 'pickupQuestions';
							$shoppingcart_question->question_id = 'pickupPlace';
							$shoppingcart_question->label = 'Pickup Place';
							$shoppingcart_question->data_type = 'READ_ONLY';
							$shoppingcart_question->answer = $activity[$i]->pickupPlace->title;
							$shoppingcart_question->order = 1;
							$shoppingcart_question->save();
						}
			}
			
			if($activity[$i]->extrasPrice>0)
			{
				for($k=0;$k<count($activity[$i]->extraBookings);$k++)
				{	
					$shoppingcart_rate = new ShoppingcartRate();
					$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
					$shoppingcart_rate->type = 'extra';
					$shoppingcart_rate->title = $activity[$i]->extraBookings[$k]->extra->title;
					$shoppingcart_rate->qty = 1;
					$shoppingcart_rate->price = $activity[$i]->extraBookings[$k]->extra->price;
					$shoppingcart_rate->unit_price = $unitPrice;
					$subtotal = $activity[$i]->extraBookings[$k]->extra->price;
					$discount = $subtotal - $activity[$i]->extraBookings[$k]->extra->discountedUnitPrice;
					$total = $subtotal - $discount;
					$shoppingcart_rate->discount = $discount;
					$shoppingcart_rate->subtotal = $subtotal;
					$shoppingcart_rate->total = $total;
					$shoppingcart_rate->currency = $contents->customerInvoice->currency;
					$shoppingcart_rate->save();
					$subtotal_product += $subtotal;
					$total_discount += $discount;
					$total_product += $total;
				}
			}
			
			
			
			ShoppingcartProduct::where('id',$shoppingcart_product->id)->update([
				'subtotal'=>$subtotal_product,
				'discount'=>$total_discount,
				'total'=>$total_product
				]);
				
			$grand_discount += $total_discount;
			$grand_subtotal += $subtotal_product;
			$grand_total += $total_product;
		}
		
		Shoppingcart::where('id',$shoppingcart->id)->update([
				'subtotal'=>$grand_subtotal,
				'discount'=>$grand_discount,
				'total'=>$grand_total
			]);
		
		


		$shoppingcart_payment = new ShoppingcartPayment();
		//$shoppingcart_payment->amount = self::convert_currency($grand_total,$contents->customerInvoice->currency,'USD');
		//$shoppingcart_payment->currency = 'USD';
		$shoppingcart_payment->amount = $grand_total;
		$shoppingcart_payment->currency = $contents->customerInvoice->currency;
		$shoppingcart_payment->payment_status = 0;
		$shoppingcart_payment->shoppingcart_id = $shoppingcart->id;
		$shoppingcart_payment->save();

		// QUESTION ==============================================================================
		// Main Question ====
		$questions = BokunHelper::get_questionshoppingcart($id);
		$mainContactDetails = $questions->mainContactDetails;
		$order = 1;
		foreach($mainContactDetails as $mainContactDetail)
		{
			
			$shoppingcart_question = new ShoppingcartQuestion();
			
			$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_question->type = 'mainContactDetails';
			$shoppingcart_question->question_id = $mainContactDetail->questionId;
			$shoppingcart_question->label = $mainContactDetail->label;
			$shoppingcart_question->data_type = $mainContactDetail->dataType;
			if(isset($mainContactDetail->dataFormat)) $shoppingcart_question->data_format = $mainContactDetail->dataFormat;
			$shoppingcart_question->required = $mainContactDetail->required;
			$shoppingcart_question->select_option = $mainContactDetail->selectFromOptions;
			$shoppingcart_question->select_multiple = $mainContactDetail->selectMultiple;
			$shoppingcart_question->order = $order;
			$shoppingcart_question->save();
			$order += 1;
			
			if($mainContactDetail->selectFromOptions=="true")
			{
				$order_option = 1;
				foreach($mainContactDetail->answerOptions as $answerOption)
				{
					
					$shoppingcart_question_option = new ShoppingcartQuestionOption();
					
					$shoppingcart_question_option->shoppingcart_questions_id = $shoppingcart_question->id;
					$shoppingcart_question_option->label = $answerOption->label;
					$shoppingcart_question_option->value = $answerOption->value;
					$shoppingcart_question_option->order = $order_option;
					$shoppingcart_question_option->save();
					$order_option += 1;
				}
			}
		}
		
		// Activity Question ====
		if(isset($questions->activityBookings))
		{
		$order = 1;
		$activityBookings = $questions->activityBookings;
		foreach($activityBookings as $activityBooking)
		{
			
			if(isset($activityBooking->pickupQuestions))
			{
				//$order = 2;
				for($i=0;$i<count($activityBooking->pickupQuestions);$i++)
				{
					//$check_pickupQuestions = ShoppingcartQuestion::where('shoppingcart_id',$shoppingcart->id)->where('type','pickupQuestions')->first();
					//if(!isset($check_pickupQuestions))
					//{
					$shoppingcart_question = new ShoppingcartQuestion();
					$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
					$shoppingcart_question->type = 'pickupQuestions';
					$shoppingcart_question->booking_id = $activityBooking->bookingId;
					$shoppingcart_question->question_id = $activityBooking->pickupQuestions[$i]->questionId;
					$shoppingcart_question->label = $activityBooking->pickupQuestions[$i]->label;
					$shoppingcart_question->data_type = $activityBooking->pickupQuestions[$i]->dataType;
					$shoppingcart_question->required = $activityBooking->pickupQuestions[$i]->required;
					$shoppingcart_question->select_option = $activityBooking->pickupQuestions[$i]->selectFromOptions;
					$shoppingcart_question->select_multiple = $activityBooking->pickupQuestions[$i]->selectMultiple;
					$shoppingcart_question->order = $order;
					$shoppingcart_question->save();
					$order += 1;
					//}
				}
			}
			
			if(isset($activityBooking->questions))
			{
				$questions = $activityBooking->questions;
				//$order = 1;
				for($i=0;$i<count($questions);$i++)
				{
					
					$shoppingcart_question = new ShoppingcartQuestion();
					
					$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
					$shoppingcart_question->type = 'activityBookings';
					$shoppingcart_question->booking_id = $activityBooking->bookingId;
					$shoppingcart_question->question_id = $questions[$i]->questionId;
					$shoppingcart_question->label = $questions[$i]->label;
					$shoppingcart_question->data_type = $questions[$i]->dataType;
					if(isset($questions[$i]->dataFormat)) $shoppingcart_question->data_format = $questions[$i]->dataFormat;
					if(isset($questions[$i]->help)) $shoppingcart_question->help = $questions[$i]->help;
					$shoppingcart_question->required = $questions[$i]->required;
					$shoppingcart_question->select_option = $questions[$i]->selectFromOptions;
					$shoppingcart_question->select_multiple = $questions[$i]->selectMultiple;
					$shoppingcart_question->order = $order;
					$shoppingcart_question->save();
					$order += 1;
					
					if($questions[$i]->selectFromOptions=="true")
					{
						$order_option = 1;
						foreach($questions[$i]->answerOptions as $answerOption)
						{
							
							$shoppingcart_question_option = new ShoppingcartQuestionOption();
							
							$shoppingcart_question_option->shoppingcart_questions_id = $shoppingcart_question->id;
							$shoppingcart_question_option->label = $answerOption->label;
							$shoppingcart_question_option->value = $answerOption->value;
							$shoppingcart_question_option->order = $order_option;
							$shoppingcart_question_option->save();
							$order_option += 1;
						}
					}
			
				}
			}
		}
		}
	}
	
	public static function update_shoppingcart($contents,$id)
	{
		$activity = $contents->activityBookings;
		$shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$id)->first();
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
		$shoppingcart->save();
		
		

		$shoppingcart->shoppingcart_products()->delete();
		//$shoppingcart->shoppingcart_products()->delete();
		//$shoppingcart->shoppingcart_payments()->delete();

		$grand_total = 0;
		$grand_subtotal = 0;
		$grand_discount = 0;
		for($i=0;$i<count($activity);$i++)
		{
			$product = Product::where('bokun_id',$activity[$i]->activity->id)->firstOrFail();

			$product_invoice = $contents->customerInvoice->productInvoices;
			$lineitems = $product_invoice[$i]->lineItems;
			
			
			$shoppingcart_product = new ShoppingcartProduct();
			$shoppingcart_product->shoppingcart_id = $shoppingcart->id;
			$shoppingcart_product->product_confirmation_code = $activity[$i]->productConfirmationCode;
			$shoppingcart_product->booking_id = $activity[$i]->id;
			$shoppingcart_product->product_id = $activity[$i]->activity->id;
			if(isset($product_invoice[$i]->product->keyPhoto->derived[2]->url))
			{
				$shoppingcart_product->image = $product_invoice[$i]->product->keyPhoto->derived[2]->url;
			}
			else
			{
				$shoppingcart_product->image = ImageHelper::thumbnail($product);
			}

			 
			$shoppingcart_product->title = $activity[$i]->activity->title;
			$shoppingcart_product->rate = $activity[$i]->rate->title;
			$shoppingcart_product->currency = $contents->customerInvoice->currency;
			$shoppingcart_product->date = self::texttodate($product_invoice[$i]->dates);
			$shoppingcart_product->save();
			
			$subtotal_product = 0;
			$total_discount = 0;
			$total_product = 0;
			for($z=0;$z<count($lineitems);$z++)
			{
					$itemBookingId = $lineitems[$z]->itemBookingId;
					$itemBookingId = explode("_",$itemBookingId);
					
					$type_product = '';
					$unitPrice = 'Price per booking';
					
					if($activity[$i]->extrasPrice>0)
					{
						$check_extra = false;
						for($k=0;$k<count($activity[$i]->extraBookings);$k++)
						{
							if($itemBookingId[1]==$activity[$i]->extraBookings[$k]->id)
							{
								$check_extra = true;
							}
							
						}
						if(!$check_extra)
						{
							if($itemBookingId[1]!="pickup")
							{
								$type_product = 'product';
								if($lineitems[$z]->title!="Passengers")
								{
									$unitPrice = $lineitems[$z]->title;
								}
							}
						}
					}
					else
					{
						if($itemBookingId[1]!="pickup")
						{
							$type_product = 'product';
							if($lineitems[$z]->title!="Passengers")
							{
								$unitPrice = $lineitems[$z]->title;
							}
						}
					}
					
					if($itemBookingId[1]=="pickup"){
						$type_product = "pickup";
					}
					
					
					
					if($type_product=="product")
					{
						
						$shoppingcart_rate = new ShoppingcartRate();
						
						$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_rate->type = $type_product;
						$shoppingcart_rate->title = $activity[$i]->activity->title;
						$shoppingcart_rate->qty = $lineitems[$z]->quantity;
						$shoppingcart_rate->price = $lineitems[$z]->unitPrice;
						$shoppingcart_rate->unit_price = $unitPrice;
						$subtotal = $lineitems[$z]->unitPrice * $shoppingcart_rate->qty;
						$discount = $subtotal - ($lineitems[$z]->discountedUnitPrice * $shoppingcart_rate->qty);
						$total = $subtotal - $discount;
						$shoppingcart_rate->discount = $discount;
						$shoppingcart_rate->subtotal = $subtotal;
						$shoppingcart_rate->total = $total;
						$shoppingcart_rate->currency = $contents->customerInvoice->currency;
						$shoppingcart_rate->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}
					
					if($type_product=="pickup")
					{
						$shoppingcart_rate = new ShoppingcartRate();
						$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
						$shoppingcart_rate->type = $type_product;
						$shoppingcart_rate->title = 'Pick-up and drop-off services';
						$shoppingcart_rate->qty = 1;
						$shoppingcart_rate->price = $lineitems[$z]->total;
						$shoppingcart_rate->unit_price = $unitPrice;
						$subtotal = $lineitems[$z]->total;
						$discount = $subtotal - $lineitems[$z]->discountedUnitPrice;
						$total = $subtotal - $discount;
						$shoppingcart_rate->discount = $discount;
						$shoppingcart_rate->subtotal = $subtotal;
						$shoppingcart_rate->total = $total;
						$shoppingcart_rate->currency = $contents->customerInvoice->currency;
						$shoppingcart_rate->save();
						
						$subtotal_product += $subtotal;
						$total_discount += $discount;
						$total_product += $total;
					}	
					
						if(isset($activity[$i]->pickupPlace->title))
						{
							$shoppingcart_question = new ShoppingcartQuestion();
							$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
							$shoppingcart_question->type = 'pickupQuestions';
							$shoppingcart_question->question_id = 'pickupPlace';
							$shoppingcart_question->label = 'Pickup Place';
							$shoppingcart_question->data_type = 'READ_ONLY';
							$shoppingcart_question->answer = $activity[$i]->pickupPlace->title;
							$shoppingcart_question->order = 1;
							$shoppingcart_question->save();
						}
			}
			
			if($activity[$i]->extrasPrice>0)
			{
				for($k=0;$k<count($activity[$i]->extraBookings);$k++)
				{	
					$shoppingcart_rate = new ShoppingcartRate();
					$shoppingcart_rate->shoppingcart_product_id = $shoppingcart_product->id;
					$shoppingcart_rate->type = 'extra';
					$shoppingcart_rate->title = $activity[$i]->extraBookings[$k]->extra->title;
					$shoppingcart_rate->qty = 1;
					$shoppingcart_rate->price = $activity[$i]->extraBookings[$k]->extra->price;
					$shoppingcart_rate->unit_price = $unitPrice;
					$subtotal = $activity[$i]->extraBookings[$k]->extra->price;
					$discount = $subtotal - $activity[$i]->extraBookings[$k]->extra->discountedUnitPrice;
					$total = $subtotal - $discount;
					$shoppingcart_rate->discount = $discount;
					$shoppingcart_rate->subtotal = $subtotal;
					$shoppingcart_rate->total = $total;
					$shoppingcart_rate->currency = $contents->customerInvoice->currency;
					$shoppingcart_rate->save();
					$subtotal_product += $subtotal;
					$total_discount += $discount;
					$total_product += $total;
				}
			}
			
			
			$shoppingcart_product->subtotal = $subtotal_product;
			$shoppingcart_product->discount = $total_discount;
			$shoppingcart_product->total = $total_product;
			$shoppingcart_product->save();

			$grand_discount += $total_discount;
			$grand_subtotal += $subtotal_product;
			$grand_total += $total_product;
		}
		
		

		$shoppingcart->subtotal = $grand_subtotal;
		$shoppingcart->discount = $grand_discount;
		$shoppingcart->total = $grand_total;
		$shoppingcart->save();

		//$shoppingcart->shoppingcart_payments->amount = self::convert_currency($grand_total,$contents->customerInvoice->currency,'USD');
		//$shoppingcart->shoppingcart_payments->currency = 'USD';
		
		$shoppingcart->shoppingcart_payment->amount = $grand_total;
		$shoppingcart->shoppingcart_payment->currency = $contents->customerInvoice->currency;
		$shoppingcart->shoppingcart_payment->save();


		//===============================================

		
		$questions = BokunHelper::get_questionshoppingcart($id);
		
		if(isset($questions->activityBookings))
		{
		$order = 1;
		$activityBookings = $questions->activityBookings;
		foreach($activityBookings as $activityBooking)
		{
			
			$check_shoppingcart_questions = ShoppingcartQuestion::where('booking_id',$activityBooking->bookingId)->get();
			if(!@count($check_shoppingcart_questions))
			{
				if(isset($activityBooking->pickupQuestions))
				{
					//$order = 2;
					for($i=0;$i<count($activityBooking->pickupQuestions);$i++)
					{
						//$check_pickupQuestions = ShoppingcartQuestion::where('shoppingcart_id',$shoppingcart->id)->where('type','pickupQuestions')->first();
						//if(!isset($check_pickupQuestions))
						//{
						$shoppingcart_question = new ShoppingcartQuestion();
						$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
						$shoppingcart_question->type = 'pickupQuestions';
						$shoppingcart_question->booking_id = $activityBooking->bookingId;
						$shoppingcart_question->question_id = $activityBooking->pickupQuestions[$i]->questionId;
						$shoppingcart_question->label = $activityBooking->pickupQuestions[$i]->label;
						$shoppingcart_question->data_type = $activityBooking->pickupQuestions[$i]->dataType;
						$shoppingcart_question->required = $activityBooking->pickupQuestions[$i]->required;
						$shoppingcart_question->select_option = $activityBooking->pickupQuestions[$i]->selectFromOptions;
						$shoppingcart_question->select_multiple = $activityBooking->pickupQuestions[$i]->selectMultiple;
						$shoppingcart_question->order = $order;
						$shoppingcart_question->save();
						$order += 1;
					//}
					}
				}
			
				if(isset($activityBooking->questions))
				{
					$questions = $activityBooking->questions;
					//$order = 1;
					for($i=0;$i<count($questions);$i++)
					{
					
						$shoppingcart_question = new ShoppingcartQuestion();
					
						$shoppingcart_question->shoppingcart_id = $shoppingcart->id;
						$shoppingcart_question->type = 'activityBookings';
						$shoppingcart_question->booking_id = $activityBooking->bookingId;
						$shoppingcart_question->question_id = $questions[$i]->questionId;
						$shoppingcart_question->label = $questions[$i]->label;
						$shoppingcart_question->data_type = $questions[$i]->dataType;
						if(isset($questions[$i]->dataFormat)) $shoppingcart_question->data_format = $questions[$i]->dataFormat;
						if(isset($questions[$i]->help)) $shoppingcart_question->help = $questions[$i]->help;
						$shoppingcart_question->required = $questions[$i]->required;
						$shoppingcart_question->select_option = $questions[$i]->selectFromOptions;
						$shoppingcart_question->select_multiple = $questions[$i]->selectMultiple;
						$shoppingcart_question->order = $order;
						$shoppingcart_question->save();
						$order += 1;
					
						if($questions[$i]->selectFromOptions=="true")
						{
							$order_option = 1;
							foreach($questions[$i]->answerOptions as $answerOption)
							{
							
								$shoppingcart_question_option = new ShoppingcartQuestionOption;
							
								$shoppingcart_question_option->shoppingcart_questions_id = $shoppingcart_question->id;
								$shoppingcart_question_option->label = $answerOption->label;
								$shoppingcart_question_option->value = $answerOption->value;
								$shoppingcart_question_option->order = $order_option;
								$shoppingcart_question_option->save();
								$order_option += 1;
							}
						}
			
					}
				}

			}
		}
		}

		//===============================================
	}
	
	public static function get_shoppingcart($id,$action="insert")
	{
		$lastsessionId = Session::get('sessionId');
		if(Session::has('sessionId')){
			$sessionId = Session::get('sessionId');
		}else{
			$sessionId = $id;
			Session::put('sessionId',$sessionId);
		}
		
		//========================================================================
		$contents = BokunHelper::get_shoppingcart($id);
		if($action=="insert") self::insert_shoppingcart($contents,$id);
		if($action=="update") self::update_shoppingcart($contents,$id);
	}
	
	public static function shoppingcart_session()
	{
		if(!Session::has('sessionId')){
            $sessionId = Uuid::uuid4()->toString();
            Session::put('sessionId',$sessionId);
        }
        return Session::get('sessionId');
	}

	public static function shoppingcart_mail($shoppingcart)
	{
		$email = $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer;
		if($email!="")
		{
			Mail::to($email)->send(new BookingConfirmedMail($shoppingcart));
		}
	}

	public static function shoppingcart_clear($shoppingcart)
	{
		BokunHelper::get_removepromocode($shoppingcart->session_id);
		foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
            {
                BokunHelper::get_removeactivity($shoppingcart->session_id,$shoppingcart_product->booking_id);
            }
        Session::forget('sessionId');
        return $shoppingcart;
	}

	public static function check_question($shoppingcart,$request)
	{
		$status = true;
		$array = array();
		foreach($shoppingcart->shoppingcart_questions()->get() as $question)
            {
            	if($request->input($question->id)=="" && $question->required)
					{
						$status = false;
						$array[$question->id] = array($question->label .' field is required.');
					}
            }
        return $array;
	}

	public static function save_question($shoppingcart,$request)
	{
		foreach($shoppingcart->shoppingcart_questions()->get() as $question)
            {
                $shoppingcart_question = ShoppingcartQuestion::find($question->id);
                $shoppingcart_question->answer = $request->input($question->id);
                $shoppingcart_question->save();
                
                if($shoppingcart_question->select_option)
                {
                    $shoppingcart_question_options = ShoppingcartQuestionOption::where('shoppingcart_questions_id',$shoppingcart_question->id)->get();
                    foreach($shoppingcart_question_options as $shoppingcart_question_option)
                    {
                        if($shoppingcart_question_option->value==$request->input($question->id))
                        {
                            $shoppingcart_question_option = ShoppingcartQuestionOption::find($shoppingcart_question_option->id);
                            $shoppingcart_question_option->answer = 1;
                            $shoppingcart_question_option->save();
                        }
                        else
                        {
                            $shoppingcart_question_option_ = ShoppingcartQuestionOption::find($shoppingcart_question_option->id);
                            $shoppingcart_question_option_->answer = 0;
                            $shoppingcart_question_option_->save();
                        }
                        
                    }
                }
            }
        return $shoppingcart;
	}

	public static function remove_promocode($shoppingcart)
	{
		BokunHelper::get_removepromocode($shoppingcart->session_id);
        self::get_shoppingcart($shoppingcart->session_id,"update");
        return $shoppingcart;
	}

	public static function remove_activity($shoppingcart,$bookingId)
	{
		ShoppingcartQuestion::where('booking_id',$bookingId)->delete();
		BokunHelper::get_removeactivity($shoppingcart->session_id,$bookingId);
		self::get_shoppingcart($shoppingcart->session_id,"update");
		return $shoppingcart;
	}

	public static function apply_promocode($shoppingcart,$promocode)
	{
		$status = false;
		$contents = BokunHelper::get_applypromocode($shoppingcart->session_id,$promocode);
		
		if(!isset($contents->fields->reason))
		{
			$status = true;
			self::get_shoppingcart($shoppingcart->session_id,"update");
		}
		return $status;
	}

	public static function get_rate($from,$to)
	{
		$amount = self::convert_currency(1,$to,$from);
		$value = '1 '. $to .' = '. $amount .' '. $from;
		return $value;
	}

	public static function convert_currency($amount,$from,$to)
	{
		$array_currency = BokunHelper::get_currency();
		$from_rate = null;
		$to_rate = null;
		
		foreach($array_currency as $struct) {
    		if ($from == $struct->code) {
        		$from_rate = $struct->rate;
        	break;
    		}
		}

		foreach($array_currency as $struct) {
    		if ($to == $struct->code) {
        		$to_rate = $struct->rate;
        	break;
    		}
		}
		
		$value = $amount * ($to_rate / $from_rate);
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
	
	
	
	public static function texttodate($str){
		$text = $str;
		$text = explode('@',$text);
		if(isset($text[1]))
		{
			$date = \DateTime::createFromFormat('D d.M Y ', $text[0]);
			$time = \DateTime::createFromFormat(' H:i', $text[1]);
			$hasil = $date->format('Y-m-d') .' '. $time->format('H:i:00');
		}
		else
		{
			$date = \DateTime::createFromFormat('D d.M Y', $text[0]);
			$hasil = $date->format('Y-m-d') .' 00:00:00';
		}
		return $hasil;
	}
	
	public static function datetotext($str){
		$date = \DateTime::createFromFormat('Y-m-d H:i:s', $str);
		if($date->format('H:i')=="00:00")
		{
			return $date->format('D d.M Y');
		}
		else
		{
			return $date->format('D d.M Y @ H:i');
		}
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
                        default:
                            $paymentStatus = "NOT AVAILABLE";
                    }
       return $paymentStatus;
	}

	public static function lang($type,$str){
		$hasil = '';
		if($type=='categories')
		{
			$hasil = str_ireplace("_"," ",ucwords(strtolower($str)));
			
		}
		if($type=='dificulty')
		{
			$hasil = str_ireplace("_"," ",ucwords(strtolower($str)));
			
		}
		if($type=='accessibility')
		{
			$hasil = str_ireplace("_"," ",ucwords(strtolower($str)));
			
		}
		if($type=='type')
		{
			switch($str)
			{
				case 'ACTIVITIES':
					$hasil = 'Day tour/Activity';
				break;
			}
			
		}
		if($type=='language')
		{
			switch($str)
			{
				case 'ja':
					$hasil = 'Japanese';
				break;
				case 'ja':
					$hasil = 'Italian';
				break;
				case 'fr':
					$hasil = 'French';
				break;
				case 'en':
					$hasil = 'English';
				break;
			}
			
		}
		return $hasil;
	}

	public static function check_shoppingcart($sessionId)
	{
		$status = false;
		$shoppingcart = Shoppingcart::where('session_id', $sessionId)->where('bookingStatus','CART')->first();
		if(@count($shoppingcart))
		{
			$check = $shoppingcart->shoppingcart_products()->count();
			if($check>0) $status = true;
		}
		
		return $status;
	}
	
	
}
?>
