<?php

namespace budisteikul\toursdk\Models;


use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class,'channel_id');
    }

}
