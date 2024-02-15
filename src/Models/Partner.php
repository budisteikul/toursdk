<?php

namespace budisteikul\toursdk\Models;
use Illuminate\Database\Eloquent\Model;


class Partner extends Model
{
    protected $table = 'partners';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['name','tracking_code','description'];

    public function shoppingcarts()
    {
        return $this->hasMany(Shoppingcart::class,'tracking_code','referer');
    }
}
