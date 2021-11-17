<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['name','slug'];
	
	public function category()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Category','category_id');
    }

    public function images()
    {
        return $this->hasMany('budisteikul\toursdk\Models\Image','product_id');
    }

    public function reviews()
    {
        return $this->hasMany('budisteikul\toursdk\Models\Review','product_id');
    }
}
