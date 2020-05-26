<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeesInReviewerPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviewer_purchases', function (Blueprint $table) {
            $table->double('payment_gateway_fee')->default(0)->after('amount');
            $table->double('service_fee')->default(0)->after('payment_gateway_fee');
            $table->double('other_fees')->default(0)->after('service_fee');
            $table->double('total')->default(0)->after('other_fees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviewer_purchases', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway_fee', 'service_fee', 'other_fees', 'total']);
        });
    }
}
