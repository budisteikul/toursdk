<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingcartPayment extends Model
{
    protected $table = 'shoppingcart_payments';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Shoppingcart');
    }
}
