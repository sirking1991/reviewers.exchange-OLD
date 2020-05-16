<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoCorrectAnswerInQuestionnaireTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->smallInteger('correct_answer_count')->default(0)->after('difficulty_level');
        });
        // set correct_answer_count
        $questionnaires = \App\Questionnaire::all();
        foreach ($questionnaires as $question) {
            $question->correct_answer_count = \App\Answer::where('questionnaire_id', $question->id)->where('is_correct', 'yes')->count();
            $question->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('correct_answer_count');
        });
    }
}
