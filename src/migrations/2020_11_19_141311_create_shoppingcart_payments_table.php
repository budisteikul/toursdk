<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingcartPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppingcart_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shoppingcart_id');
            $table->foreign('shoppingcart_id')
                    ->references('id')->on('shoppingcarts')
                    ->onDelete('cascade')->onUpdate('cascade');
            
            //$table->string('type')->default('PAYPAL');        
            $table->string('order_id')->nullable();
            $table->string('authorization_id')->nullable();
            $table->float('amount')->default(0);
            $table->string('currency')->default('USD');
            //$table->float('rate')->default(0);

            $table->tinyInteger('payment_status')->default(1);
            

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
        Schema::dropIfExists('shoppingcart_payments');
    }
}
