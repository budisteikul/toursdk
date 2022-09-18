<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;


class Calendar extends Model
{
    protected $table = 'calendars';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['google_calendar_id','product_id','date','people'];

    public static function boot()
    {
        parent::boot();

        self::created(function($model){
               
        });

        self::deleting(function($model){
               
        });

        
    }
}

