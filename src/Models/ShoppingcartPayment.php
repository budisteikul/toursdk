<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingcartPayment extends Model
{
    protected $table = 'shoppingcart_payments';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = [
    	'shoppingcart_id',
    	'payment_provider',
    	'payment_type',
    	'bank_name',
    	'bank_code',
    	'va_number',
    	'snaptoken',
    	'order_id',
    	'authorization_id',
    	'amount',
    	'currency',
    	'rate',
    	'rate_from',
    	'rate_to',
    	'payment_status'
    ];

    public function shoppingcart()
    {
        return $this->belongsTo(Shoppingcart::class);
    }
}
