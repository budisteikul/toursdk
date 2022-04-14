<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Http\Request;
use budisteikul\toursdk\Models\Voucher;
use budisteikul\toursdk\Models\Product;
use Illuminate\Support\Facades\Cache;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\ProductHelper;

class VoucherHelper {

	public static function check_voucher($promocode)
	{
		$status = false;
		$voucher = Voucher::where('code',strtoupper($promocode))->first();
		if ($voucher !== null) {
   			$status = true;
		}
		return $status;
	}


	public static function can_apply_voucher($product_id,$voucher_id,$type)
	{
		$status = false;
		$product = Product::where('bokun_id',$product_id)->first();
        if($product!=null)
        {
            $aaa = $product->vouchers()->where('voucher_id', $voucher_id)->where('type', $type)->get();
            if(@count($aaa)>0)
            {
            	$status = true;
            }
        }
		return $status;
	}

	public static function apply_voucher_fix($sessionId,$promocode)
	{
			$status = false;

			$shoppingcart = Cache::get('_'. $sessionId);
    		$voucher = Voucher::where('code',strtoupper($promocode))->first();

    		$shoppingcart_discount = 0;
			$shoppingcart_due_now = 0;
			$shoppingcart_due_on_arrival = 0;
    		foreach($shoppingcart->products as $product) 
			{

				$jumlah = 0;
				foreach($product->product_details as $product_detail)
				{
					if(self::can_apply_voucher($product->product_id,$voucher->id,$product_detail->type))
					{
						$jumlah++;
					}
				}

				$product_discount = 0;
				foreach($product->product_details as $product_detail)
				{

					$discount = 0;
					if(self::can_apply_voucher($product->product_id,$voucher->id,$product_detail->type))
					{
						$status = true;
						$discount = $voucher->amount / $jumlah;
					}

					$total = $product_detail->subtotal - $discount;
					$product_discount += $discount;

					$product_detail->discount = $discount;
					$product_detail->total = $total;
				}

				$product->discount = $product_discount;
						
				$product->total = $product->subtotal - $product->discount;

				$deposit = BookingHelper::get_deposit($product->product_id,$product->total);
				$product->due_now = $deposit->due_now;
				$product->due_on_arrival = $deposit->due_on_arrival;

				$shoppingcart_discount += $product->discount;
				$shoppingcart_due_now += $product->due_now;
				$shoppingcart_due_on_arrival += $product->due_on_arrival;

			}

			$shoppingcart->discount = $shoppingcart_discount;
			$shoppingcart->total = $shoppingcart->total - $shoppingcart->discount;
			$shoppingcart->due_now = $shoppingcart_due_now;
			$shoppingcart->due_on_arrival = $shoppingcart_due_on_arrival;

			$shoppingcart->promo_code = strtoupper($promocode);
			
			Cache::forget('_'. $sessionId);
			Cache::add('_'. $sessionId, $shoppingcart, 172800);

			return $status;
	}

	public static function apply_voucher_percentage($sessionId,$promocode)
	{
				$status = false;

				$shoppingcart = Cache::get('_'. $sessionId);
    			$voucher = Voucher::where('code',strtoupper($promocode))->first();

				$shoppingcart_discount = 0;
				$shoppingcart_due_now = 0;
				$shoppingcart_due_on_arrival = 0;
				foreach($shoppingcart->products as $product) 
				{
						$product_discount = 0;
						
						foreach($product->product_details as $product_detail)
						{
							$discount = 0;

							if(self::can_apply_voucher($product->product_id,$voucher->id,$product_detail->type))
							{
								$status = true;
								$discount = $product_detail->subtotal * $voucher->amount / 100;
							}

							$total = $product_detail->subtotal - $discount;
							$product_discount += $discount;

							$product_detail->discount = $discount;
							$product_detail->total = $total;
						
						}

						$product->discount = $product_discount;
						
					$product->total = $product->subtotal - $product->discount;

					$deposit = BookingHelper::get_deposit($product->product_id,$product->total);
					$product->due_now = $deposit->due_now;
					$product->due_on_arrival = $deposit->due_on_arrival;

					$shoppingcart_discount += $product->discount;
					$shoppingcart_due_now += $product->due_now;
					$shoppingcart_due_on_arrival += $product->due_on_arrival;
				}

				$shoppingcart->discount = $shoppingcart_discount;
				$shoppingcart->total = $shoppingcart->total - $shoppingcart->discount;
				$shoppingcart->due_now = $shoppingcart_due_now;
				$shoppingcart->due_on_arrival = $shoppingcart_due_on_arrival;

				$shoppingcart->promo_code = strtoupper($promocode);
			
        		
				Cache::forget('_'. $sessionId);
				Cache::add('_'. $sessionId, $shoppingcart, 172800);

				return $status;

	}

	public static function shoppingcart($id)
	{
		$shoppingcart = Cache::get('_'. $id);
		if($shoppingcart->promo_code!=null)
		{
			$status = VoucherHelper::apply_voucher($shoppingcart->session_id,$shoppingcart->promo_code);
			if(!$status)
			{
				$shoppingcart->promo_code = null;
				Cache::forget('_'. $id);
				Cache::add('_'. $id, $shoppingcart, 172800);
			}
		}
	}

	public static function apply_voucher($sessionId,$promocode)
    {
    	$status = false;
    	if(self::check_voucher($promocode))
		{
			$voucher = Voucher::where('code',strtoupper($promocode))->first();
    		if($voucher->is_percentage)
			{
				$status = self::apply_voucher_percentage($sessionId,$promocode);
			}
			else
			{
				$status = self::apply_voucher_fix($sessionId,$promocode);
			}
		}
    	return $status;
    }

    public static function remove_voucher($sessionId)
	{
		$shoppingcart = Cache::get('_'. $sessionId);

		$due_now_product = 0;
		$due_on_arrival_product = 0;
		foreach($shoppingcart->products as $product) 
		{
			foreach($product->product_details as $product_detail)
			{
				$product_detail->discount = 0;
				$product_detail->total = $product_detail->subtotal;
			}

			$deposit = BookingHelper::get_deposit($product->product_id,$product->subtotal);
			$product->discount = 0;
			$product->total = $product->subtotal;
			$product->due_now = $deposit->due_now;
			$product->due_on_arrival = $deposit->due_on_arrival;

			$due_now_product += $product->due_now;
			$due_on_arrival_product += $product->due_on_arrival;
		}

		$shoppingcart->discount = 0;
		$shoppingcart->total = $shoppingcart->subtotal;
		$shoppingcart->due_now = $due_now_product;
		$shoppingcart->due_on_arrival = $due_on_arrival_product;

		$shoppingcart->promo_code = null;

		Cache::forget('_'. $sessionId);
		Cache::add('_'. $sessionId, $shoppingcart, 172800);
		return $shoppingcart;
	}
}
?>