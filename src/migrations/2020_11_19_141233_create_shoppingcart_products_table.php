<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingcartProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppingcart_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shoppingcart_id');
            $table->foreign('shoppingcart_id')
                  ->references('id')->on('shoppingcarts')
                  ->onDelete('cascade')->onUpdate('cascade');

                
            $table->string('booking_id')->nullable();    
            $table->string('product_confirmation_code')->nullable();
            
            $table->string('product_id')->nullable();
            $table->string('image')->nullable();
            $table->string('title')->nullable();
            $table->string('rate')->nullable();
            $table->dateTime('date')->nullable();
            
            $table->string('currency')->default('USD');
            $table->float('subtotal',24,2)->default(0);
            $table->float('discount',24,2)->default(0);
            $table->float('tax',24,2)->default(0);
            $table->float('fee',24,2)->default(0);
            $table->float('admin',24,2)->default(0);
            $table->float('total',24,2)->default(0);

            $table->float('due_now',24,2)->default(0);
            $table->float('due_on_arrival',24,2)->default(0);

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
        Schema::dropIfExists('shoppingcart_products');
    }
}
