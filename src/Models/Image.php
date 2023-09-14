<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'images';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['public_id','secure_url','sort'];

    public function product()
    {
    	return $this->belongsTo(Product::class,'product_id','id');
    }
}
