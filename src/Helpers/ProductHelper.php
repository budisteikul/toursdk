<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Helpers\CategoryHelper;
use Carbon\Carbon;

class ProductHelper {

    public static function product_name($product_id)
    {
        $product = Product::where('product_id',$product_id)->first();
        return $product->name;
    }

    public static function getProductByCategory($category_id)
    {
        $array_category = CategoryHelper::getChild($category_id);
        $array = array();
        for($i=0;$i<count($array_category);$i++)
        {
            $products = Product::where('category_id',$array_category[$i])->get();
            foreach($products as $product)
            {
                array_push($array,$product->id);
            }
        }
        $products = Product::orderBy('updated_at','desc')->findMany($array);
        return $products;
    }

    public static function get_product_id($productId)
    {
        $product_id = null;
        $product = Product::where('bokun_id',$productId)->first();
        if ($product !== null) {
            $product_id = $product->id;
        }
        return $product_id;
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
                case 'in':
                    $hasil = 'Indonesian';
                break;
                case 'de':
                    $hasil = 'German';
                break;
            }
            
        }
        return $hasil;
    }

    public static function texttodate($text){
        
        $hasil = null;
        $arr_date = array();

        if($text=="Never expires") return null;

        if (str_contains($text, '@')) {
            $arr_date = explode('@',$text);
        }
        else if (str_contains($text, '-')) {
            $arr_date = explode('-',$text);
        }
        else
        {
            $arr_date[] = $text;
        }


        if(isset($arr_date[1]))
        {
            $date = Carbon::createFromFormat('D, F d Y', trim($arr_date[0]));
            $time = date("H:i", strtotime(trim($arr_date[1])));
            $time = Carbon::createFromFormat('H:i', $time);
            $hasil = $date->format('Y-m-d') .' '. $time->format('H:i:00');
        }
        else
        {
            $date = Carbon::createFromFormat('D, F d Y', trim($arr_date[0]));

            $hasil = $date->format('Y-m-d') .' 00:00:00';
        }
        return $hasil;
        
    }
    
    public static function datetotext($str){
        if($str==null) return null;
        $date = date("Y-m-d H:i:s", strtotime(trim($str)));
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $date);
        if($date->format('H:i')=="00:00")
        {
            return $date->format('D d.M Y');
        }
        else
        {
            return $date->format('D d.M Y @ H:i');
        }
    }    

}
?>
