<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCartSessionIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!(Schema::hasColumn(config('laracart.database.table'), 'cart_session_id'))) {
            Schema::table(config('laracart.database.table'), function (Blueprint $table) {
                $table->string('cart_session_id')->nullable()->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ((Schema::hasColumn(config('laracart.database.table'), 'cart_session_id'))) {
            Schema::table(config('laracart.database.table'), function (Blueprint $table) {
                $table->dropColumn('cart_session_id');
            });
        }
    }
}
