<?php

namespace budisteikul\toursdk\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    //use HasFactory;
    protected $table = 'disbursements';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = [
    						'transaction_id',
    						'vendor_id',
    						'vendor_name',
    						'bank_code',
    						'account_number',
    						'amount',
    						'reference',
    						'status'
    					  ];
}
