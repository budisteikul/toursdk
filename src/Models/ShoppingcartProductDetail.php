<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingcartProductDetail extends Model
{
    protected $table = 'shoppingcart_product_details';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart_product()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\ShoppingcartProduct','shoppingcart_product_id','id');
    }
	
	public function shoppingcart()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Shoppingcart','shoppingcart_id','id');
    }
}
