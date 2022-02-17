<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['name','contact_person','phone','email','account_holder','account_number','bank_code'];

    public function scopeWhereLike($query, $column, $value)
	{
		if(env('DB_CONNECTION')=="pgsql")
		{
			return $query->where($column, 'ILIKE', '%'.$value.'%');
		}
		else
		{
			return $query->where($column, 'LIKE', '%'.$value.'%');
		}
    	
	}

}
