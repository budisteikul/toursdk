<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\ShoppingcartProduct;

class ReportHelper {

    public static function traveller_product_per_year($title,$year)
    {
        $total = 0;
        $products = ShoppingcartProduct::with('shoppingcart')
        ->WhereHas('shoppingcart', function($query) {
                 $query->where('booking_status','CONFIRMED');
            })->where('title',$title)->whereYear('date',$year)->whereMonth('date',$month)->get();
        
        foreach($products as $product)
        {
            foreach($product->shoppingcart_product_details as $shoppingcart_product_detail)
            {
                $people = $shoppingcart_product_detail->people;
                $total += $people;
            }
        }
        return $total;
    }

    public static function traveller_per_day($day,$month,$year)
    {
        $total = 0;
        $products = ShoppingcartProduct::with('shoppingcart')
        ->WhereHas('shoppingcart', function($query) {
                 $query->where('booking_status','CONFIRMED');
            })->whereYear('date',$year)->whereMonth('date',$month)->whereDay('date',$day)->get();
        
        foreach($products as $product)
        {
            foreach($product->shoppingcart_product_details as $shoppingcart_product_detail)
            {
                $people = $shoppingcart_product_detail->people;
                $total += $people;
            }
        }
        return $total;
    }

    public static function traveller_per_month($month,$year)
    {
        $total = 0;
        $products = ShoppingcartProduct::with('shoppingcart')
        ->WhereHas('shoppingcart', function($query) {
                 $query->where('booking_status','CONFIRMED');
            })->whereMonth('date',$month)->whereDay('date',$day)->get();
        
        foreach($products as $product)
        {
            foreach($product->shoppingcart_product_details as $shoppingcart_product_detail)
            {
                $people = $shoppingcart_product_detail->people;
                $total += $people;
            }
        }
        return $total;
    }

    public static function traveller_product_per_month($title,$month,$year)
    {
        $total = 0;
        $products = ShoppingcartProduct::with('shoppingcart')
        ->WhereHas('shoppingcart', function($query) {
                 $query->where('booking_status','CONFIRMED');
            })->where('title',$title)->whereYear('date',$year)->whereMonth('date',$month)->get();
        
        foreach($products as $product)
        {
            foreach($product->shoppingcart_product_details as $shoppingcart_product_detail)
            {
                $people = $shoppingcart_product_detail->people;
                $total += $people;
            }
        }
        return $total;
    }

}
?>