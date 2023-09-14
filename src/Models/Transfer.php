<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfers';
    protected $fillable = ['idr', 'usd', 'status', 'wise_id'];
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function recipient()
    {
        return $this->hasOne(Recipient::class,'wise_id','wise_id');
    }
}
