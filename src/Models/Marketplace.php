<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $table = 'marketplaces';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
