<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterShoppingcartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->float('due_now')->after('total')->default(0);
            $table->float('due_on_arrival')->after('due_now')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->dropColumn('due_now');
            $table->dropColumn('due_on_arrival');
        });
    }
}
