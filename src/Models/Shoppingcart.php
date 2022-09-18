<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;
//use budisteikul\toursdk\Helpers\CalendarHelper;

class Shoppingcart extends Model
{
    protected $table = 'shoppingcarts';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart_products()
    {
        return $this->hasMany(ShoppingcartProduct::class,'shoppingcart_id','id');
    }
	
	public function shoppingcart_questions()
    {
        return $this->hasMany(ShoppingcartQuestion::class,'shoppingcart_id','id');
    }
	
	public function shoppingcart_payment()
    {
        return $this->hasOne(ShoppingcartPayment::class,'shoppingcart_id','id');
    }

    public function calendar()
    {
        return $this->hasOne(Calendar::class,'shoppingcart_id','id');
    }

    public static function boot()
    {
        parent::boot();

        self::updated(function($model){
               //CalendarHelper::create_calendar($model->confirmation_code);
        });

        
    }
}
