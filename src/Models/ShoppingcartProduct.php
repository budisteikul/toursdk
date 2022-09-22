<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;
use budisteikul\toursdk\Helpers\CalendarHelper;

class ShoppingcartProduct extends Model
{
    protected $table = 'shoppingcart_products';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart()
    {
        return $this->belongsTo(Shoppingcart::class);
    }
	
	public function shoppingcart_product_details()
    {
        return $this->hasMany(ShoppingcartProductDetail::class,'shoppingcart_product_id');
    }

    public static function boot()
    {
        parent::boot();

        self::updating(function($model){
               CalendarHelper::create_calendar($model->shoppingcart->confirmation_code);
        });
    }
}
