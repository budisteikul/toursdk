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
            
            $table->string('payment_provider')->nullable();

            $table->string('payment_type')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('va_number')->nullable();
            $table->string('snaptoken')->nullable();
            $table->string('qrcode')->nullable();

            $table->string('order_id')->nullable();
            $table->string('authorization_id')->nullable();

            $table->float('amount',24,2)->default(0);
            $table->string('currency')->default('USD');
            $table->float('rate',24,2)->default(0);
            $table->string('rate_from')->nullable();
            $table->string('rate_to')->nullable();

            $table->string('link')->nullable();
            $table->string('redirect')->nullable();
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
