<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingcartProduct extends Model
{
    protected $table = 'shoppingcart_products';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Shoppingcart');
    }
	
	public function shoppingcart_rates()
    {
        return $this->hasMany('budisteikul\toursdk\Models\ShoppingcartRates','shoppingcart_product_id');
    }
}
