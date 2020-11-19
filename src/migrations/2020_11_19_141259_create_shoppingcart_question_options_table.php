<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingcartQuestionOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppingcart_question_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shoppingcart_question_id');
            $table->foreign('shoppingcart_question_id')
                    ->references('id')->on('shoppingcart_questions')
                    ->onDelete('cascade')->onUpdate('cascade');
            
            $table->string('label')->nullable();
            $table->string('value')->nullable();
            $table->string('order')->nullable();
            $table->tinyInteger('answer')->default(0);

            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shoppingcart_question_options');
    }
}
