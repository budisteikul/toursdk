<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterShoppingcartPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shoppingcart_payments', function (Blueprint $table) {
            $table->float('rate')->after('currency')->default(0);
            $table->string('rate_from')->after('rate')->nullable();
            $table->string('rate_to')->after('rate_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shoppingcart_payments', function (Blueprint $table) {
             $table->dropColumn('rate');
             $table->dropColumn('rate_from');
             $table->dropColumn('rate_to');
        });
    }
}
