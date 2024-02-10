<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionAnswerPair;

class QuestionAnswerPairController extends Controller
{
    private QuestionAnswerPair $qap;

    public function __construct(QuestionAnswerPair $qap) {
        $this->qap = $qap;
        $this->middleware('auth:sanctum');
    }


    // private funcs
    function basicTokenizer(string $text): array
    {
    $text = preg_replace("/[^\p{L}\p{Nd}\s]/u", " ", $text);
    $text = preg_replace("/\s+/u", " ", $text);
    return $text;
}

    private function validateTrainerOrAdmin(Request $request) {
        $user = $request->user();
        if ($user->trainer == 0 || $user->superadmin == 0) {
            return false;
        }
        return true;
    }
    // trainer and admin functions
    public function getUnanswered(Request $request) {
        $user = $request->user();
        if (!$this->validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers and Admins can access unanswered questions. If you are an admin please report this.'], 400);
        }

        $query = $this->qap->query();
        $query = $query->where('answered', null);
        $data = $query->get();

        return response()->json(['message' => 'success', 'data' => $data]);
    }

    public function answerQuestion(Request $request) {
        try {
            $validatedData = $request->validate([
                'qap_id' => 'required|exists:question_answer_pairs,id',
                'answer' => 'required|string|min:10',
                'question_rename' => 'string',
            ]);
        if (!$this->validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers and Admins can answer questions. If you are an admin please report this.'], 400);
        }
        $qap = $this->qap->find($request->qap_id);
        $qap->answer = $request->answer;
        $qap->tokens = basicTokenizer($request->question);
        $user = $request->user();
        $qap->answerer_id = $user->id;
        if (isset($request->question_rename)) {
            $qap->question = $request->question_rename;
        }
        $qap->save();
        return response()->json(['message' => 'Success']);

    } catch (ValidationException $exception) {
        $errors = $exception->validator->errors();
        return response()->json(['errors' => $errors], 422);
        }
    }

    public function getExistingAnswer(Request $request) {
        try {
            $validatedData = $request->validate([
                'qap_id' => 'required|int|exists:question_answer_pairs,id',
            ]);

        if (!$this->validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers and Admins can answer questions. If you are an admin please report this.'], 400);
        }
        if ($this->qap->find($request->qap_id)->answer == null) {
            return response()->json(['message' => 'The question exists but has not yet been answered.'], 400);
        }

        $qap = $this->qap->find($request->qap_id);
        return response()->json(['message' => 'Success', 'data' => $qap->answer]);

        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function postQuestionAndAnswer(Request $request) {
        try {
            $validatedData = $request->validate([
                'question' => 'required|string',
                'language_id' => 'required|exists:languages,id',
                'topic_id' => 'required|exists:topics,id',
                'answer' => 'required',
            ]);

        if (!$this->validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers and Admins can submit here. If you are an admin please report this.'], 400);
        }
            $userobj = $request->user();
            $newQap = new QuestionAnswerPair;
            $newQap->question = $request->question;
            $newQap->tokens = basicTokenizer($request->question);
            $newQap->language_id = $request->language_id;
            $newQap->topic_id = $request->language_id;
            $newQap->answer = $request->answer;
            $newQap->answerer_id = $userobj->id;
            $newQap->save();
            return response()->json(['message' => 'Success'], 200);

        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function deleteQuestionAndAnswer(Request $request) {
        try {
            $validatedData = $request->validate([
                'qap_id' => 'required|exists:question_answer_pairs, id',
            ]);

        if (!$this->validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers and Admins can delete FAQs'], 400);
        }
            $qap = $this->qap->find($request->id);
            $qap->delete();

        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
    }

    // student functions

    public function askQuestion(Request $request) {
        try {
            $validatedData = $request->validate([
                'question' => 'required|string',
                'language_id' => 'required|numeric',
                'topic_id' => 'required|numeric',
            ]);
            $userobj = $request->user();
            $newQap = new QuestionAnswerPair;
            $newQap->question = $request->question;
            $newQap->tokens = basicTokenizer($request->question);
            $newQap->language_id = $request->language_id;
            $newQap->topic_id = $request->language_id;
            return response()->json(['message' => 'Question saved successfully'], 201);
        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
            }
    }


    public function getAnswers(Request $request) {
    try {
        // Retrieve FAQ posts from the database
        $faqPosts = $this->qap->all();

        $results = $faqPosts;
        if (isset($request->searchtokens)) {
            $jsonString = str_replace("'", '"', $request->searchtokens);
            $queryArray = json_decode($jsonString, false);
            return response()->json(["Message" => "Successfully retrieved", "Data" => $queryArray], 200);
            $results = $this->tokenSort($queryArray); //token sort is below
        }

        if (isset($request->language_id)) {
            $results = $results->where('language_id', $request->language_id);
        }

        if (isset($request->topic_id)) {
            $results = $results->where('topic_id', $request->topic_id);
        }

        $output = $this->serialize($results);

        return response()->json(["Message" => "Successfully retrieved", "Data" => $output], 200);
    } catch (Exception $e) {
        return response()->json(["Message" => "Error", "Errors" => $e->getMessage()], 200);
    }
    }

    private function serialize($arr_of_answers) {
        $serialized = [];

        foreach ($arr_of_answers as $answer) {
            $serialized[] = [
                "question" => $answer->question,
                "answer" => $answer->answer,
                "answerer_id" => $answer->answerer_id,
            ];
        }
        return $serialized;
    }

    private function tokenSort(array $queryArray) {
// This function is heavy for non async, remember to define a maximum request frequency on FE
        // If it feels janky then honestly rewrite on FE even at 0(n ^ 2) the input size is never going to be high enough to be concerning.
        // Calculate relevance scores for each FAQ post
         $rankedPosts = [];
         foreach ($queryArray as $qap) {
             $postTokens = explode(" ", strtolower($qap->tokens)); // need to add functionality for posting questions to auto create tokens
             $matches = array_intersect($userTokens, $postTokens);
             $score = count($matches); // Simple scoring based on token matches
             $rankedPosts[] = [
                 'post' => $qap,
                 'score' => $score
             ];
         }

         // Sort FAQ posts based on relevance scores
         usort($rankedPosts, function ($a, $b) {
             return $b['score'] - $a['score']; // Sort in descending order of scores
         });

         return $rankedPosts;
    }
}
