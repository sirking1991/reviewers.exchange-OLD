<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reviewer;
use App\Questionnaire;
use App\LearningMaterial;
use App\Answer;
use App\QuestionnaireGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
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

    public function reviewerList(Request $request)
    {
        $list = Reviewer::where('name', 'like', "%{$request->search}%")
            ->where('user_id', Auth()->user()->id)
            ->paginate(10);

        return view('publisher/reviewers-list', ['list' => $list, 'search' => $request->search]);
    }

    public function reviewerShow($id = null)
    {
        $record = Reviewer::where('user_id', Auth()->user()->id)
            ->where('id', $id)
            ->first();

        if (!$record) $record = new Reviewer();

        return view('publisher/reviewer-show', ['record' => $record]);
    }

    public function reviewerSave(Request $request)
    {
        $request->validate([
            'reviewer_name' => ['required'],
        ]);

        $record = Reviewer::where('user_id', Auth()->user()->id)
            ->where('id', $request->id)
            ->first();
        if (!$record) {
            $record = new Reviewer();
            $record->user_id = Auth()->user()->id;
            $record->cover_photo = "common/reviewers_bg/bg_" . rand(1, 10) . ".jpg";
        }

        if ('' == $record->cover_photo)
            $record->cover_photo = "common/reviewers_bg/bg_" . rand(1, 10) . ".jpg";
        $record->name = $request->reviewer_name;
        $record->status = $request->status;
        $record->category = $request->category;
        $record->questionnaires_to_display = $request->questionnaires_to_display;
        $record->time_limit = $request->time_limit;
        $record->price = $request->price;

        if (isset($request->remove_cover_photo) && 'yes' == $request->remove_cover_photo) {            
            try {
                Log::debug('deleting cover photo');
                // if not a common cover photo, then delete
                if (false === strpos($record->cover_photo, 'common/reviewers_bg/bg_')) $this->deleteImage($record->cover_photo);
                // set random cover_photo
                $record->cover_photo = "common/reviewers_bg/bg_" . rand(1, 10) . ".jpg";
                Log::debug('cover photo set to '.$record->cover_photo);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        if ('undefined' != $request->cover_photo && '' != $request->cover_photo  ) {
            try {
                $record->cover_photo = $this->uploadImage('images/reviewers/cover_photo', $request->cover_photo);
                Log::debug('cover photo set to '.$record->cover_photo);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }        
        $record->save();

        $request->session()->flash('status', 'Record saved');

        return redirect('/publisher/reviewers/' . $record->id);
    }

    public function reviewerDelete($id)
    {
        $record = Reviewer::where('user_id', Auth()->user()->id)
            ->where('id', $id)
            ->first();

        if (!$record) return response('', 404);

        $record->delete();

        return response()->json();
    }

    public function saveLearningMaterial(Request $request, String $reviewerId, String $id)
    {
        if (0 == $id) {
            Log::debug('creating a new learning-material record');
            $learningMaterial = LearningMaterial::create([
                'user_id' => Auth()->user()->id,
                'reviewer_id' => $reviewerId,
                'title' => $request->title ?? '',
                'content' => $request->content ?? '',
            ]);
        } else {
            Log::debug('updating learning-material record');
            $learningMaterial = LearningMaterial::where('reviewer_id', $reviewerId)->where('id', $id)->first();

            if(!$learningMaterial) return response('Learning material not found', 404);

            $learningMaterial->update([
                'title' => $request->title ?? '',
                'content' => $request->content ?? '',
            ]);
        }

        return LearningMaterial::where('reviewer_id', $reviewerId)->get();
    }

    public function deleteLearningMaterial(String $reviewerId, String $questionId)
    {
        Log::debug('deleting Learning Material ' . $questionId);

        $question = LearningMaterial::where('user_id', Auth()->user()->id)
            ->where('reviewer_id', $reviewerId)
            ->where('id', $questionId)
            ->first();

        if (!$question) return response('', 404);

        Answer::where('questionnaire_id', $questionId)->delete();
        $question->delete();

        return LearningMaterial::where('reviewer_id', $reviewerId)->get();
    }

    public function saveQuestion(Request $request, String $reviewerId, String $questionId)
    {
        if (0 == $questionId) {
            Log::debug('create a new question record');
            $question = Questionnaire::create([
                'user_id' => Auth()->user()->id,
                'reviewer_id' => $reviewerId,
                'questionnaire_group_id' => $request->input('questionnaire_group_id') ?? '0',
                'learning_material_id' => $request->input('learning_material_id') ?? '0',
                'question' => $request->input('question') ?? '',
                'image' => '',
                'randomly_display_answers' => $request->input('randomly_display_answers') ?? 'no',
                'difficulty_level' => $request->input('difficulty_level') ?? 'normal',
            ]);
            if (null != $request->input('answers')) {
                $answers = json_decode($request->input('answers'));
                $correctAnswerCount = 0;
                foreach ($answers  as $index => $answer) {
                    if ('yes' == $answer->is_correct) $correctAnswerCount++;
                    $ans = Answer::create([
                        'questionnaire_id' => $question->id,
                        'answer' => $answer->answer,
                        'is_correct' => $answer->is_correct,
                        'answer_explanation' => $answer->answer_explanation ?? '',
                    ]);
                    // check if image was attach to the answer
                    if ('undefined' != $request->{'answer_image_' . $index}) {
                        try {
                            $ans->image = $this->uploadImage('images/answer/' . $ans->id, $request->{'answer_image_' . $index});
                            $ans->save();
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                        }
                    }
                }
                $question->correct_answer_count = $correctAnswerCount;
                $question->save();
            }
        } else {
            Log::debug('updating question record');
            $question = Questionnaire::where('reviewer_id', $reviewerId)
                ->where('id', $questionId)
                ->first();

            if (!$question) return response('Questionnaire not found', 404);

            $question->update([
                'questionnaire_group_id' => $request->input('questionnaire_group_id') ?? '0',
                'learning_material_id' => $request->input('learning_material_id') ?? '0',
                'question' => $request->input('question') ?? '',
                'randomly_display_answers' => $request->input('randomly_display_answers') ?? 'no',
                'difficulty_level' => $request->input('difficulty_level') ?? 'normal',
            ]);
            // update/create answers
            if (null != $request->input('answers') || 0 == count($request->input('answers'))) {
                $answers = json_decode($request->input('answers'));
                $safeAnswerId = [];
                $correctAnswerCount = 0;
                foreach ($answers  as $index => $answer) {
                    if ('yes' == $answer->is_correct) $correctAnswerCount++;
                    if (isset($answer->id)) {
                        Answer::where('id', $answer->id)
                            ->update([
                                'answer' => $answer->answer ?? '',
                                'is_correct' => $answer->is_correct ?? 'no',
                                'answer_explanation' => $answer->answer_explanation ?? '',
                            ]);
                        $answerId = $answer->id;
                    } else {
                        $answerModel = Answer::create([
                            'questionnaire_id' => $questionId,
                            'answer' => $answer->answer ?? '',
                            'is_correct' => $answer->is_correct ?? 'no',
                            'answer_explanation' => $answer->answer_explanation ?? '',
                        ]);
                        $answerId = $answerModel->id;
                    }
                    $safeAnswerId[] = $answerId;
                    // remove answer image?
                    if (isset($answer->remove_image) && 'yes' == $answer->remove_image) {
                        try {
                            $this->deleteImage($answer->image);
                            Answer::find($answerId)->update(['image' => '']);
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                        }
                    }
                    // check if image was attach to the answer
                    if ('undefined' != $request->{'answer_image_' . $index}) {
                        try {
                            $path = $this->uploadImage('images/answer/' . $answerId, $request->{'answer_image_' . $index});
                            Answer::find($answerId)->update(['image' => $path]);
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                        }
                    }
                }
                $question->correct_answer_count = $correctAnswerCount;
                $question->save();
                // delete other answers that where not part of the answer submitted
                Answer::where('questionnaire_id', $questionId)->whereNotIn('id', $safeAnswerId)->delete();
            } else {
                // delete all existing answers
                Answer::where('questionnaire_id', $questionId)->delete();
            }
        }

        // remove image?
        if (isset($request->remove_image) && 'yes' == $request->remove_image) {
            try {
                $this->deleteImage($question->image);
                $question->image = '';
                $question->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        // if uploaded new file
        if ('undefined' != $request->image) {
            // delete existing file so we dont polute the s3 folder
            if ('' != $question->image) {
                try {
                    $this->deleteImage($question->image);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }

            // upload uploaded file
            try {
                $question->image = $this->uploadImage('images/questionnaire/' . $questionId, $request->image);
                $question->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return Questionnaire::where('reviewer_id', $reviewerId)
            ->with('answers')
            ->get();
    }

    private function uploadImage(String $fileUnder, $image)
    {
        try {
            return Storage::disk('s3')->put(env('APP_ENV') . '/' . $fileUnder, $image, 'public');
        } catch (\Exception $e) {
            throw new \Exception("Error uploading image: " . $e->getMessage());
        }
    }    

    private function deleteImage(String $image)
    {
        try {
            Storage::disk('s3')->delete($image);
        } catch (\Exception $e) {
            throw new \Exception("Error deleting image: {$image} error=" . $e->getMessage());
        }
    }

    public function deleteQuestion(String $reviewerId, String $questionId)
    {
        Log::debug('deleting question ' . $questionId);

        $question = Questionnaire::where('user_id', Auth()->user()->id)
            ->where('reviewer_id', $reviewerId)
            ->where('id', $questionId)
            ->first();

        if (!$question) return response('', 404);

        Answer::where('questionnaire_id', $questionId)->delete();
        $question->delete();

        return Questionnaire::where('reviewer_id', $reviewerId)
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
        return QuestionnaireGroup::where('reviewer_id', $reviewerId)->get();
    }

    public function questionnaireGroupSave(Request $request, $reviewerId, $questionnaireGroupId)
    {
        if (0 == $questionnaireGroupId) {
            Log::debug('create a new question group record');
            $questionGroup = QuestionnaireGroup::create([
                'user_id' => Auth()->user()->id,
                'reviewer_id' => $reviewerId,
                'name' => $request->input('name') ?? '',
                'content' => $request->input('content') ?? '',
                'randomly_display_questions' => $request->input('randomly_display_questions') ?? 'no',
            ]);
        } else {
            Log::debug('updating question record');
            $questionGroup = QuestionnaireGroup::where('user_id', Auth()->user()->id)
                ->where('id', $questionnaireGroupId)
                ->first();

            if (!$questionGroup) return response('QuestionnaireGroup not found', 404);

            $questionGroup->update([
                'name' => $request->input('name') ?? '',
                'content' => $request->input('content') ?? '',
                'randomly_display_questions' => $request->input('randomly_display_questions') ?? 'no',
            ]);
        }

        return QuestionnaireGroup::where('reviewer_id', $reviewerId)->get();
    }

    public function questionnaireGroupDelete(String $reviewerId, String $questionnaireGroupId)
    {
        Log::debug('deleting questionaire group ' . $questionnaireGroupId);

        $questionnaireGroup = QuestionnaireGroup::where('user_id', Auth()->user()->id)
            ->where('id', $questionnaireGroupId)
            ->first();

        if (!$questionnaireGroup) return response('', 404);

        $questionnaireGroup->delete();

        // remove group in question
        Questionnaire::where('questionnaire_group_id', $questionnaireGroupId)
            ->where('reviewer_id', $reviewerId)
            ->update(['questionnaire_group_id' => 0]);

        return QuestionnaireGroup::where('reviewer_id', $reviewerId)->get();
    }  
    
    public function requestFundWithdrawal(Request $request)
    {
        // check if has enough balance
        $add = DB::table('transactions')->where('user_id', Auth()->user()->id)->sum('add');
        $sub = DB::table('transactions')->where('user_id', Auth()->user()->id)->sum('sub');
        $balance = $add - $sub;
        $requestAmount = (int)$request->amount;

        if ($requestAmount > $balance) {
            return response('Requested amount is greater than current balance', 400);
        }

        if ($requestAmount < env('MINIMUM_BALANCE_FOR_WITHDRAWAL', 500)) {
            return response('Requested amount is less than the minimum balance for withdrawal', 400);
        }

        // delete existing pending request
        \App\WithdrawalRequest::where('user_id', Auth()->user()->id)
                    ->where('status', 'pending')
                    ->delete();

        // create request
        \App\WithdrawalRequest::create([
            'user_id' => Auth()->user()->id,
            'amount' => $requestAmount,
            'status' => 'pending'
        ]);

        return response('success');
    }

}
