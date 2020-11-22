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
        return $this->belongsTo('budisteikul\toursdk\Models\Product','product_id');
    }

    public function channel()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Channel','channel_id');
    }

}
