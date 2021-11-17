<?php

namespace budisteikul\toursdk\Models;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $table = 'promo_codes';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['code','value','discount_type','product_id','product_type','limit','booking_from','booking_to','travel_from','travel_to','status'];

}
