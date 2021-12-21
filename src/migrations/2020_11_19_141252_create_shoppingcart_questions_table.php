<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingcartQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppingcart_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shoppingcart_id');
            $table->foreign('shoppingcart_id')
                  ->references('id')->on('shoppingcarts')
                  ->onDelete('cascade')->onUpdate('cascade');
                    
            $table->string('type')->nullable();
            $table->string('when_to_ask')->default('booking');
            $table->string('booking_id')->nullable();
            $table->string('question_id')->nullable();
            $table->string('label')->nullable();
            $table->string('data_type')->nullable();
            $table->string('data_format')->nullable();
            $table->string('required')->nullable();
            $table->string('select_option')->nullable();
            $table->string('select_multiple')->nullable();
            $table->text('help')->nullable();
            $table->text('answer')->nullable();
            $table->string('order')->nullable();

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
        Schema::dropIfExists('shoppingcart_questions');
    }
}
