<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingcartPayment extends Model
{
    protected $table = 'shoppingcart_payments';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['shoppingcart_id','amount','currency','rate','rate_from','rate_to','payment_provider','snaptoken','redirect_url','payment_status'];

    public function shoppingcart()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Shoppingcart');
    }
}
