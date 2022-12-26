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
        return $this->belongsTo(Category::class,'category_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class,'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class,'product_id');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class,'vouchers_products','product_id','voucher_id')->withPivot('type')->withTimestamps();
    }
}
