<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReviewerPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviewer_purchases', function (Blueprint $table) {
            $table->string('reference')->index()->after('id');
            $table->string('gateway_trans_id')->index()->after('reference');
            $table->string('status')->index()->after('reference')->default('pending');
            $table->text('raw_request_data')->after('amount');
            $table->text('raw_response_data')->after('raw_request_data');
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
            $table->dropColumn('reference');
            $table->dropColumn('gateway_trans_id');
            $table->dropColumn('status');
            $table->dropColumn('raw_request_data');
            $table->dropColumn('raw_response_data');
        });
    }
}
