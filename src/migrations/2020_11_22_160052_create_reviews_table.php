<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id');
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreignId('channel_id');
            $table->foreign('channel_id')
                ->references('id')->on('channels')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->string('user')->nullable();
            $table->string('title')->nullable();
            $table->text('text')->nullable();
            $table->float('rating')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('link')->nullable();
            
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
        Schema::dropIfExists('reviews');
    }
}
