<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\ShoppingcartProduct;

class ReportHelper {

    public static function traveller_per_year($product_id,$year)
    {
        $total = 0;
        $products = ShoppingcartProduct::where('product_id',$product_id)->whereYear('date',$year)->whereMonth('date',$month)->get();
        
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

    public static function traveller_per_month($product_id,$month,$year)
    {
        $total = 0;
        $products = ShoppingcartProduct::where('product_id',$product_id)->whereYear('date',$year)->whereMonth('date',$month)->get();
        
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