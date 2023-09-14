<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
}
