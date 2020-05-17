<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamResultQuestioAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_result_question_answers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('exam_result_id');
            $table->bigInteger('questionnaire_id');
            $table->string('is_correct_answer')->default('no');
            $table->text('raw_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_result_question_answers');
    }
}
