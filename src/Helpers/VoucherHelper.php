<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Http\Request;
use budisteikul\toursdk\Models\Voucher;
use Illuminate\Support\Facades\Cache;

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

	public static function apply_voucher($sessionId,$promocode)
    {
    	if(self::check_voucher($promocode))
		{
			$shoppingcart = Cache::get('_'. $sessionId);
    		$voucher = Voucher::where('code',strtoupper($promocode))->first();

    		if($voucher->is_percentage)
			{
				$shoppingcart_discount = 0;
				$shoppingcart_total = 0;
				$shoppingcart_due_now = 0;
				$shoppingcart_due_on_arrival = 0;
				foreach($shoppingcart->products as $product) 
				{
					$product_discount = 0;
					$product_total = 0;

					foreach($product->product_details as $product_detail)
					{
						if($product_detail->type=="product")
						{
							$subtotal = $product_detail->subtotal;
							$discount = $product_detail->subtotal * $amount / 100;
							$total = $subtotal - $discount;

							$product_discount += $discount;
							$product_total += $total;

							$product_detail->discount = $discount;
							$product_detail->total = $total;
						}
						
					}
					
					$deposit = self::get_deposit($product->product_id,$product_totala);
					$product->discount = $product_discount;
					$product->total = $product_total;
					$product->due_now = $deposit->due_now;
					$product->due_on_arrival = $deposit->due_on_arrival;

					$shoppingcart_discount += $product->discount;
					$shoppingcart_total += $product->total;
					$shoppingcart_due_now += $product->due_now;
					$shoppingcart_due_on_arrival += $product->due_on_arrival;
				}

				$shoppingcart->discount = $shoppingcart_discount;
				$shoppingcart->total = $shoppingcart_total;
				$shoppingcart->due_now = $shoppingcart_due_now;
				$shoppingcart->due_on_arrival = $shoppingcart_due_on_arrival;

				$shoppingcart->promo_code = strtoupper($promocode);
			}
        		
			Cache::forget('_'. $sessionId);
			Cache::add('_'. $sessionId, $shoppingcart, 172800);
		}
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

			$deposit = self::get_deposit($product->product_id,$product->subtotal);
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