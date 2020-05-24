<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Aceraven777\PayMaya\Model\Checkout\Item;
use Aceraven777\PayMaya\Model\Checkout\ItemAmount;
use Aceraven777\PayMaya\Model\Checkout\ItemAmountDetails;
use App\Libraries\PayMaya\User as PayMayaUser;

use App\ExamResult;

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
        $reviewer = \App\Reviewer::find($reviewerId);

        // check if reviewer exist
        if (!$reviewer) return response('', 404);

        // check if user has purchase he reviewer
        $reviewerPurchased = \App\ReviewerPurchase::where('user_id', Auth()->user()->id)
            ->where('reviewer_id',  $reviewer->id)
            ->get();
        if (0 == count($reviewerPurchased)) return response('User has not purchased the reviewer', 404);

        $questionnaires = \App\Questionnaire::where('reviewer_id', $reviewer->id)
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
            'questions' => ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('questions'),
            'correct_answers' => ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('correct_answers'),
            'wrong_answers' => ExamResult::where('reviewer_id', $reviewerId)->where('user_id', Auth()->user()->id)->sum('wrong_answers'),
        ]);
    }

    public function buyReviewer($reviewerId)
    {
        // TODO: check if user has no yet purchase this reviewer
        $reviewer = \App\Reviewer::find($reviewerId);
        if (!$reviewer) return redirect('home');

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
        \App\ReviewerPurchase::where('user_id', Auth()->user()->id)->where('status', 'pending')->update(['status' => 'cancelled']);

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
