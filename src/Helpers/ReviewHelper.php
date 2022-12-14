<?php
namespace budisteikul\toursdk\Helpers;

class ReviewHelper {

	public static function star($rating){
			switch($rating)
					{
						case '1':
							$star ='<i class="fa fa-star text-warning"></i>';	
						break;
						case '2':
							$star ='<i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i>';	
						break;
						case '3':
							$star ='<i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i>';	
						break;
						case '4':
							$star ='<i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i>';	
						break;
						case '5':
							$star = '<i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i>';
						break;
						default:
							$star = '<i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i><i class="fa fa-star text-warning"></i>';	
					}
			return $star;
	}

	public static function product_have_review($product){
        $status = false;
        if($product->reviews()->exists()) $status = true;
        return $status;
    }

    public static function channel_have_review($channel){
        $status = false;
        if($channel->reviews()->exists()) $status = true;
        return $status;
    }
}