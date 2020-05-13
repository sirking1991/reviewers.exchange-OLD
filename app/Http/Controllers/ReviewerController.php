<?php

namespace App\Http\Controllers;

use App\Reviewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    /**
     * Show the application dashboard.
     * @param  Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminList(Request $request)
    {
        $list = \App\Reviewer::where('name', 'like', "%{$request->search}%")
            ->where('user_id', Auth()->user()->id)
            ->paginate(10);

        return view('admin/reviewers-list', ['list' => $list, 'search' => $request->search]);
    }

    /**
     * Display the specified resource.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function adminShow($id = null)
    {
        $record = \App\Reviewer::where('user_id', Auth()->user()->id)
            ->where('id', $id)
            ->first();

        if (!$record) $record = new \App\Reviewer();

        return view('admin/reviewers-show', ['record' => $record]);
    }

    /**
     * UpSave record
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $request->validate([
            'name' => ['required'],
        ]);

        $record = Reviewer::where('user_id', Auth()->user()->id)
            ->where('id', $request->id)
            ->first();
        if (!$record) {
            $record = new Reviewer();
            $record->user_id = Auth()->user()->id;
        }

        $record->name = $request->name;
        $record->status = $request->status;
        $record->questionnaires_to_display = $request->questionnaires_to_display;
        $record->time_limit = $request->time_limit;
        $record->price = $request->price;
        $record->save();

        $request->session()->flash('status', 'Record saved');

        return redirect('/admin/reviewers/' . $record->id);
    }

    public function delete($id)
    {
        $record = Reviewer::where('user_id', Auth()->user()->id)
            ->where('id', $id)
            ->first();

        if (!$record) return response('', 404);

        $record->delete();

        return response()->json();
    }

    public function saveQuestion(Request $request, String $reviewerId, String $questionId)
    {
        if (0 == $questionId) {
            Log::debug('create a new question record');
            $question = \App\Questionnaire::create([
                'user_id' => Auth()->user()->id,
                'reviewer_id' => $reviewerId,
                'questionnaire_group_id' => $request->input('questionnaire_group_id') ?? '0',
                'question' => $request->input('question') ?? '',
                'randomly_display_answers' => $request->input('randomly_display_answers') ?? 'no',
                'difficulty_level' => $request->input('difficulty_level') ?? 'normal',
            ]);
            if (null != $request->input('answers')) {
                foreach ($request->input('answers') as $answer) {
                    \App\Answer::create([
                        'questionnaire_id' => $question->id,
                        'answer' => $answer['answer'],
                        'is_correct' => $answer['is_correct'],
                        'answer_explanation' => $answer['answer_explanation'] ?? '',
                    ]);
                }
            }
        } else {
            Log::debug('updating question record');
            $question = \App\Questionnaire::where('user_id', Auth()->user()->id)
                ->where('id', $questionId)
                ->first();

            if (!$question) return abort(404);

            $question->update([
                'questionnaire_group_id' => $request->input('questionnaire_group_id') ?? '0',
                'question' => $request->input('question') ?? '',
                'randomly_display_answers' => $request->input('randomly_display_answers') ?? 'no',
                'difficulty_level' => $request->input('difficulty_level') ?? 'normal',
            ]);
            // update/create answers
            if (null != $request->input('answers') || 0 == count($request->input('answers'))) {
                $updateDate = date('Y-m-d H:i:s');
                foreach ($request->input('answers') as $answer) {
                    if (isset($answer['id'])) {
                        Log::debug('updating answer');
                        \App\Answer::where('id', $answer['id'])
                            ->update([
                                'answer' => $answer['answer'] ?? '',
                                'is_correct' => $answer['is_correct'] ?? 'no',
                                'answer_explanation' => $answer['answer_explanation'] ?? '',
                                'updated_at' => $updateDate,
                            ]);
                    } else {
                        Log::debug('creating new answer');
                        \App\Answer::create([
                            'questionnaire_id' => $questionId,
                            'answer' => $answer['answer'] ?? '',
                            'is_correct' => $answer['is_correct'] ?? 'no',
                            'answer_explanation' => $answer['answer_explanation'] ?? '',
                        ]);
                    }
                }
                // delete other answers that where not part of the answer submitted
                \App\Answer::where('questionnaire_id', $questionId)
                    ->where('updated_at', '!=', $updateDate)
                    ->delete();
            } else {
                // delete all existing answers
                \App\Answer::where('questionnaire_id', $questionId)->delete();
            }
        }

        return \App\Questionnaire::where('user_id', Auth()->user()->id)
            ->where('reviewer_id', $reviewerId)
            ->with('answers')
            ->get();
    }

    public function deleteQuestion(String $reviewerId, String $questionId)
    {
        Log::debug('deleting question ' . $questionId);

        $question = \App\Questionnaire::where('user_id', Auth()->user()->id)
            ->where('id', $questionId)
            ->first();

        if (!$question) return response('', 404);

        \App\Answer::where('questionnaire_id', $questionId)->delete();
        $question->delete();

        return \App\Questionnaire::where('user_id', Auth()->user()->id)
            ->where('reviewer_id', $reviewerId)
            ->with('answers')
            ->get();
    }

    /**
     * Returns list of questionnaire_groups in json format
     * 
     * @param  String $reviewerId
     * @return  \Illuminate\Http\Response
     */
    public function questionnaireGroups(String $reviewerId)
    {
        return \App\QuestionnaireGroup::where('reviewer_id', $reviewerId)->get();
    }

    public function questionnaireGroupSave(Request $request, $reviewerId, $questionnaireGroupId)
    {
        if (0 == $questionnaireGroupId) {
            Log::debug('create a new question group record');
            $questionGroup = \App\QuestionnaireGroup::create([
                'user_id' => Auth()->user()->id,
                'reviewer_id' => $reviewerId,
                'name' => $request->input('name') ?? '',
                'content' => $request->input('content') ?? '',
                'randomly_display_questions' => $request->input('randomly_display_questions') ?? 'no',
            ]);            
        } else {
            Log::debug('updating question record');
            $questionGroup = \App\QuestionnaireGroup::where('user_id', Auth()->user()->id)
                ->where('id', $questionnaireGroupId)
                ->first();

            if (!$questionGroup) return abort(404);

            $questionGroup->update([
                'name' => $request->input('name') ?? '',
                'content' => $request->input('content') ?? '',
                'randomly_display_questions' => $request->input('randomly_display_questions') ?? 'no',
            ]);          
        }

        return \App\QuestionnaireGroup::where('reviewer_id', $reviewerId)->get();
    }

    public function questionnaireGroupDelete(String $reviewerId, String $questionnaireGroupId)
    {
        Log::debug('deleting questionaire group ' . $questionnaireGroupId);

        $questionnaireGroup = \App\QuestionnaireGroup::where('user_id', Auth()->user()->id)
            ->where('id', $questionnaireGroupId)
            ->first();

        if (!$questionnaireGroup) return response('', 404);
        
        $questionnaireGroup->delete();

        // remove group in question
        \App\Questionnaire::where('questionnaire_group_id', $questionnaireGroupId)
            ->where('reviewer_id', $reviewerId)
            ->update(['questionnaire_group_id'=>0]);

        return \App\QuestionnaireGroup::where('reviewer_id', $reviewerId)->get();
    }
}
