<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;


use App\Models\Question;

class QuestionController extends Controller
{
    private Question $question;

    public function __construct(Question $question) {
        $this->question = $question;

        $this->middleware('auth:sanctum');
    }

    public function askQuestion(Request $request) {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string',
                'content' => 'required|string',
                'language_id' => 'required|numeric',
                'topic_id' => 'required|numeric',
            ]);
            $userobj = $request->user();
            $newQuestion = new Question;
            $newQuestion->title = $request->title;
            $newQuestion->content = $request->content;
            $newQuestion->language_id = $request->language_id;
            $newQuestion->topic_id = $request->topic_id;
            $newQuestion->user_id = $userobj->id;
            $newQuestion->save();
            return response()->json(['message' => 'Question saved successfully'], 201);
        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
            }
    }

    public function getQuestions(Request $request) {
        $request->validate([
            'language_id' => 'exists:languages,id',
            'topic_id' => 'exists:topics,id',
            'answered' => 'integer|min:0|max:1',
        ]);
        $query = $this->question->query();

        if (isset($request->answered))
        {
            $query = $query->where('answered', intval($request->answered));
        }

        if ($request->language_id)
        {
            $query = $query->where('language_id', $request->language_id);
            if ($request->topic_id) {
                $query = $query->where('topic_id', $request->topic_id);
            }
        }

        $questions = $query->get();
        // $serialized_questions = $this->serializeAll($questions);

        if (count($questions) === 0)
        {
            return response()->json([
                'message' => 'No Questions Found'
            ], 404);
        }
        return response()->json(["message" => "Questions successfully retrieved", "data" => $questions], 200);
    }


}
