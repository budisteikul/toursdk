<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloseOut extends Model
{
    use HasFactory;

    protected $table = 'close_outs';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['date'];
}
