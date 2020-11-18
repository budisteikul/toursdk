<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'images';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['name','url','sort'];
    //protected $fillable = ['name','height','width','mimetype','size','path','url','short'];

    public function product()
    {
    	return $this->belongsTo('budisteikul\toursdk\Models\Product','product_id','id');
    }
}
