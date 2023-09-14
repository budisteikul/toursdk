<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingcartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppingcarts', function (Blueprint $table) {
            $table->id();

            $table->string('booking_status')->default('CART');
            $table->string('session_id')->nullable();
            $table->string('booking_channel')->nullable();
            
            $table->string('confirmation_code')->nullable();
            $table->string('promo_code')->nullable();
            
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
        Schema::dropIfExists('shoppingcarts');
    }
}
