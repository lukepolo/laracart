<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCartSessionIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!(Schema::hasColumn('users', 'cart_session_id'))) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('cart_session_id')->default(null);
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
        if ((Schema::hasColumn('users', 'cart_session_id'))) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('cart_session_id');
            });
        }
    }
}
