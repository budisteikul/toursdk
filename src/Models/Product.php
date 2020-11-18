<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['name','slug'];
	
	public function Categories()
    {
        return $this->belongsToMany('budisteikul\toursdk\Models\Category','category_product','product_id','category_id')->withTimestamps();
    }
}
