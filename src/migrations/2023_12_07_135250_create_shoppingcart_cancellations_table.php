<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppingcart_cancellations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shoppingcart_id');
            $table->foreign('shoppingcart_id')
                    ->references('id')->on('shoppingcarts')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->float('amount',24,2)->default(0);
            $table->tinyText('status')->default(0);
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
        Schema::dropIfExists('shoppingcart_cancellations');
    }
};
