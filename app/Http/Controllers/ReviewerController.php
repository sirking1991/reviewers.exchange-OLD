<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Reviewer;
use App\ExamResult;
use App\ReviewerPurchase;
use App\Questionnaire;
use App\ExamResultQuestionAnswer;

class ReviewerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function generateExam(String $reviewerId)
    {
        $reviewer = Reviewer::find($reviewerId);

        // check if reviewer exist
        if (!$reviewer) return response('', 404);

        // check if user has purchase he reviewer
        $reviewerPurchased = ReviewerPurchase::where('user_id', Auth()->user()->id)
            ->where('reviewer_id',  $reviewer->id)
            ->get();
        if (0 == count($reviewerPurchased)) return response('User has not purchased the reviewer', 404);

        $questionnaires = Questionnaire::where('reviewer_id', $reviewer->id)
            ->where('correct_answer_count', '>', 0)
            ->inRandomOrder()
            ->limit($reviewer->questionnaires_to_display)
            ->with('answers')
            ->with('questionnaireGroup')
            ->get();

        return response()->json(['reviewer' => $reviewer, 'questionnaire' => $questionnaires]);
    }

    public function saveExamResult(Request $request)
    {
        // dd($request->reviewer);
        $examResult = \App\ExamResult::create([
            'reviewer_id' => $request->reviewer['id'],
            'user_id' => Auth()->user()->id,
            'taken_on' => date('Y-m-d H:i:s'),
            'questions' => count($request->questionnaire),
        ]);

        $correct = 0;
        $wrong = 0;
        foreach ($request->questionnaire as $question) {
            if ('yes' == $question['correctly_answered']) $correct++;
            if ('no' == $question['correctly_answered']) $wrong++;
            ExamResultQuestionAnswer::create([
                'exam_result_id' => $examResult->id,
                'questionnaire_id' => $question['id'],
                'is_correct_answer' => $question['correctly_answered'],
                'raw_data' => json_encode($question)
            ]);
        }

        $examResult->correct_answers = $correct;
        $examResult->wrong_answers = $wrong;
        $examResult->save();

        return response()->json();
    }

    public function userExamSummary(Request $request, $reviewerId)
    {
        return response()->json([
            'questions' => ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('questions'),
            'correct_answers' => ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('correct_answers'),
            'wrong_answers' => ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('wrong_answers'),
        ]);
    }
    
}
