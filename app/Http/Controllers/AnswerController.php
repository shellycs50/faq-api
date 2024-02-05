<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Answer;

class AnswerController extends Controller
{
    private Answer $answer;

    public function __construct(Answer $answer) {
        $this->answer = $answer;
        $this->middleware('auth:sanctum');
    }

    public function answerQuestion(Request $request) {
        $request->validate([
            'question_id'=> 'required|exists:questions,id',
            'content' => 'required',
        ]);
        $userobj = $request->user();
        if ($userobj->trainer == 0 && $userobj->superadmin == 0) {
            return response()->json(["Error" => "You must be a trainer to post an answer"], 422);
        }
        $newAnswer = new Answer;
        $newAnswer->question_id = $request->question_id;
        $newAnswer->content = $request->content;
        $newAnswer->answerer_id = $userobj->id;

        $newAnswer->save();

        return response()->json(["Message" => "Answer Successfully posted"], 200);
    }
}
