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
            $table->string('type')->after('rate_to')->nullable();
            $table->string('method')->after('type')->default('full_payment');
            $table->float('deposit')->after('method')->default(0);
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
             $table->dropColumn('type');
             $table->dropColumn('method');
             $table->dropColumn('deposit');
        });
    }
}
