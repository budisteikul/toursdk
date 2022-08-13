<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $table = 'calendars';
    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = ['shoppingcart_id','google_calendar_id'];

    public function shoppingcart()
    {
        return $this->belongsTo(Shoppingcart::class);
    }
    
    public static function boot()
    {
        parent::boot();

        self::created(function($model){
                //FirebaseHelper::upload($model->shoppingcart()->first(),'receipt');
        });

        self::updated(function($model){
                //FirebaseHelper::upload($model->shoppingcart()->first(),'receipt');
        });

        
    }
}

