<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;
use budisteikul\toursdk\Helpers\FirebaseHelper;

class ShoppingcartCancellation extends Model
{
    protected $table = 'shoppingcart_cancellations';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = [
    	'shoppingcart_id',
    	'amount',
    	'status'
    ];

    public function shoppingcart()
    {
        return $this->belongsTo(Shoppingcart::class);
    }

}
