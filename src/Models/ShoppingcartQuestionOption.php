<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingcartQuestionOption extends Model
{
    protected $table = 'shoppingcart_question_options';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart_question()
    {
        return $this->belongsTo(ShoppingcartQuestion::class,'shoppingcart_question_id','id');
    }
}
