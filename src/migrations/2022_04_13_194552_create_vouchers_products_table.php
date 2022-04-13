<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('voucher_id');
            $table->foreign('voucher_id')
                  ->references('id')->on('vouchers')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreignId('product_id');
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->string('type')->default('product');
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
        Schema::dropIfExists('vouchers_products');
    }
}
