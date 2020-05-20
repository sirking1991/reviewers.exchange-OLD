<?php

namespace App\Http\Controllers;

use App\Reviewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Aceraven777\PayMaya\Model\Checkout\Item;
use Aceraven777\PayMaya\Model\Checkout\ItemAmount;
use Aceraven777\PayMaya\Model\Checkout\ItemAmountDetails;
use App\Libraries\PayMaya\User as PayMayaUser;

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
            'reviewer_name' => ['required'],
        ]);

        $record = Reviewer::where('user_id', Auth()->user()->id)
            ->where('id', $request->id)
            ->first();
        if (!$record) {
            $record = new Reviewer();
            $record->user_id = Auth()->user()->id;
            $record->cover_photo = "https://lares-reviewers.s3-ap-southeast-1.amazonaws.com/common/reviewers_bg/bg_" . rand(1,10) . ".jpg";
        }

        if(''==$record->cover_photo)
            $record->cover_photo = "https://lares-reviewers.s3-ap-southeast-1.amazonaws.com/common/reviewers_bg/bg_" . rand(1,10) . ".jpg";
        $record->name = $request->reviewer_name;
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
                'image' => '',
                'randomly_display_answers' => $request->input('randomly_display_answers') ?? 'no',
                'difficulty_level' => $request->input('difficulty_level') ?? 'normal',
            ]);
            if (null != $request->input('answers')) {
                $answers = json_decode($request->input('answers'));
                $correctAnswerCount = 0;
                foreach ($answers  as $index => $answer) {
                    if ('yes'==$answer->is_correct) $correctAnswerCount++;
                    $ans = \App\Answer::create([
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
                $answers = json_decode($request->input('answers'));
                $safeAnswerId = [];
                $correctAnswerCount = 0;
                foreach ($answers  as $index => $answer) {
                    if ('yes'==$answer->is_correct) $correctAnswerCount++;
                    if (isset($answer->id)) {
                        \App\Answer::where('id', $answer->id)
                            ->update([
                                'answer' => $answer->answer ?? '',
                                'is_correct' => $answer->is_correct ?? 'no',
                                'answer_explanation' => $answer->answer_explanation ?? '',
                            ]);
                        $answerId = $answer->id;
                    } else {
                        $answerModel = \App\Answer::create([
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
                            \App\Answer::find($answerId)->update(['image' => '']);
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                        }
                    }
                    // check if image was attach to the answer
                    if ('undefined' != $request->{'answer_image_' . $index}) {
                        try {
                            $path = $this->uploadImage('images/answer/' . $answerId, $request->{'answer_image_' . $index});
                            \App\Answer::find($answerId)->update(['image' => $path]);
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                        }
                    }
                }
                $question->correct_answer_count = $correctAnswerCount;
                $question->save();                
                // delete other answers that where not part of the answer submitted
                \App\Answer::where('questionnaire_id', $questionId)->whereNotIn('id', $safeAnswerId)->delete();
            } else {
                // delete all existing answers
                \App\Answer::where('questionnaire_id', $questionId)->delete();
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

        return \App\Questionnaire::where('user_id', Auth()->user()->id)
            ->where('reviewer_id', $reviewerId)
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
            ->update(['questionnaire_group_id' => 0]);

        return \App\QuestionnaireGroup::where('reviewer_id', $reviewerId)->get();
    }

    public function generateExam(String $reviewerId)
    {
        $reviewer = \App\Reviewer::find($reviewerId);

        // check if reviewer exist
        if (!$reviewer) return response('', 404);

        // check if user has purchase he reviewer
        $reviewerPurchased = \App\ReviewerPurchase::where('user_id', Auth()->user()->id)
            ->where('reviewer_id',  $reviewer->id)
            ->get();
        if(0==count($reviewerPurchased)) return response('User has not purchased the reviewer', 404);

        $questionnaires = \App\Questionnaire::where('reviewer_id', $reviewer->id)
            ->where('correct_answer_count', '>', 0)
            ->inRandomOrder()
            ->limit($reviewer->questionnaires_to_display)
            ->with('answers')
            ->with('questionnaireGroup')
            ->get();

        return response()->json(['reviewer'=>$reviewer, 'questionnaire'=>$questionnaires]);
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
            if ('yes'==$question['correctly_answered']) $correct++;
            if ('no'==$question['correctly_answered']) $wrong++;
            \App\ExamResultQuestionAnswer::create([
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
            'questions'=>\App\ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('questions'),
            'correct_answers'=>\App\ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('correct_answers'),
            'wrong_answers'=>\App\ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('wrong_answers'),
            ]);
    }

    public function buyReviewer($reviewerId)
    {
        // TODO: check if user has no yet purchase this reviewer
        $reviewer = \App\Reviewer::find($reviewerId);
        if(!$reviewer) return redirect('home');

        // create the item and customer object
        $itemName = $reviewer->name;
        $totalPrice = $reviewer->price + 
                        (env('PAYMAYA_ADDON_AMOUNT') 
                        + (env('PAYMAYA_ADDON_RATE') * $reviewer->price) 
                        + (env('CONVINIENCE_FEE_RATE') * $reviewer->price));
    
        $userPhone = '';
        $userEmail = Auth()->user()->email;
        
        $reference = Auth()->user()->id . '-' . date('YmdHis');
    
        // Item
        $itemAmountDetails = new ItemAmountDetails();
        $itemAmountDetails->tax = "0.00";
        $itemAmountDetails->subtotal = number_format($totalPrice, 2, '.', '');
        $itemAmount = new ItemAmount();
        $itemAmount->currency = "PHP";
        $itemAmount->value = $itemAmountDetails->subtotal;
        $itemAmount->details = $itemAmountDetails;
        $item = new Item();
        $item->name = $itemName;
        $item->amount = $itemAmount;
        $item->totalAmount = $itemAmount;    
        
        $user = new PayMayaUser();
        $user->contact->phone = $userPhone;
        $user->contact->email = $userEmail;

        $paymaya = new \App\Http\Controllers\PaymayaController();

        $checkout = $paymaya->checkout($user, $item, $itemAmount, $reference);
        $gatewayPaymentObject = $checkout->retrieve();
        
        // cancell all 'pending' order
        \App\ReviewerPurchase::where('user_id', Auth()->user()->id)->where('status', 'pending')->update(['status'=>'cancelled']);

        // create new order
        \App\ReviewerPurchase::create([
            'reference' => $reference,
            'gateway_trans_id' => $gatewayPaymentObject['id'],
            'reviewer_id' => $reviewerId,
            'user_id' => Auth()->user()->id,
            'amount' => $reviewer->price,
            'raw_request_data' => json_encode($gatewayPaymentObject),
            'raw_response_data' => '',
        ]);

        return redirect()->to($checkout->url);
    }
}
