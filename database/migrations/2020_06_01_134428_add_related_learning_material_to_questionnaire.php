<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelatedLearningMaterialToQuestionnaire extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->bigInteger('learning_material_id')->default(0)->index()->after('questionnaire_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('learning_material_id');
        });
    }
}
