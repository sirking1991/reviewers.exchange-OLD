<?php

namespace App\Http\Controllers;

use App\Reviewer;
use Illuminate\Http\Request;

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
        $list = \App\Reviewer::where('name', 'like', "%{$request->search}%")->paginate(10);

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
        $record = \App\Reviewer::find($id);

        // if(!$record) abort(404);
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

        $record = Reviewer::find($request->id);
        if (!$record) {
            $record = new Reviewer();
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
        $record = Reviewer::find($id);

        if (!$record) return response('', 404);


        $record->delete();

        return response()->json();
    }

    public function saveQuestion(Request $request, String $reviewerId, String $questionId)
    {
        dd($reviewerId, $questionId, $request->all());

        if (0 == $questionId) {
            // create a new question record
            $question = \App\Questionnaire::create([
                'reviewer_id' => $reviewerId,
                'question' => $request->input('question') ?? '',
                'remarks' => $request->input('remarks') ?? '',
                'randomly_display_answers' => $request->input('randomly_display_answers') ?? 'no',
                'difficulty_level' => $request->input('difficulty_level') ?? 'normal',
            ]);
            if (null != $request->input('answers')) {
                foreach ($request->input('answers') as $answer) {
                    \App\Answer::create([
                        'questionnaire_id' => $question->id,
                        'answer' => $answer->{'answer'},
                        'is_correct' => $answer->{'is_correct'},
                        'answer_explanation' => $answer->{'answer_explanation'},
                    ]);
                }
            }
        } else {
            // updating question record
            
        }
    }
}
