<?php

namespace budisteikul\toursdk\Models;
use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $table = 'recipients';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function transfer()
    {
        return $this->belongsTo(Transfer::class,'wise_id','wise_id');
    }
}
