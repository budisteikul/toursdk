<?php

namespace budisteikul\toursdk\Models;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['name','slug'];

    public function ChildCategories()
    {
        return $this->hasMany('budisteikul\toursdk\Models\Category','parent_id','id');
    }

    public function ParentCategory()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Category','parent_id','id');
    }
	
	public function Products()
    {
        return $this->belongsToMany('budisteikul\toursdk\Models\Product','category_product','category_id','product_id')->withTimestamps();
    }

    public function Product()
    {
        return $this->hasOne('budisteikul\toursdk\Models\Product','category_id');
    }
}
