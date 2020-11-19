<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Shoppingcart extends Model
{
    protected $table = 'shoppingcarts';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart_products()
    {
        return $this->hasMany('budisteikul\toursdk\Models\ShoppingcartProduct','shoppingcart_id','id');
    }
	
	public function shoppingcart_questions()
    {
        return $this->hasMany('budisteikul\toursdk\Models\ShoppingcartQuestion','shoppingcart_id','id');
    }
	
	public function shoppingcart_payment()
    {
        return $this->hasOne('budisteikul\toursdk\Models\ShoppingcartPayment','shoppingcart_id','id');
    }
}
