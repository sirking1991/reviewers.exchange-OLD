<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->after('remember_token')->default('')->index();
            $table->string('depository_bank')->after('display_name')->default('')->index();
            $table->string('account_nmbr')->after('depository_bank')->defaul('');
            $table->string('account_name')->after('account_nmbr')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'depository_bank', 'account_nmbr', 'account_name']);
        });
    }
}
