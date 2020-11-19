<?php

namespace budisteikul\toursdk\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingcartQuestion extends Model
{
    protected $table = 'shoppingcart_questions';
	protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function shoppingcart()
    {
        return $this->belongsTo('budisteikul\toursdk\Models\Shoppingcart');
    }
	
	public function shoppingcart_question_options()
    {
        return $this->hasMany('budisteikul\toursdk\Models\ShoppingcartQuestionOptions','shoppingcart_question_id','id');
    }
}
