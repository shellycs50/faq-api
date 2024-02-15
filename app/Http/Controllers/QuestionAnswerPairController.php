<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionAnswerPair;
use App\Models\Language;

class QuestionAnswerPairController extends Controller
{
    private QuestionAnswerPair $qap;
    private Language $language;
    public function __construct(QuestionAnswerPair $qap, Language $language) {
        $this->qap = $qap;
        $this->middleware('auth:sanctum');
        $this->language = $language;
    }


    // private funcs
    private function basicTokenizer(string $text): string
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
        $query = $query->where('answer', null);
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
        $qap->tokens = $this->basicTokenizer($request->question);
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

    public function getExistingAnswer(int $qap_id, Request $request) {
        if (!$this->validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers and Admins can answer questions. If you are an admin please report this.'], 400);
        }
        $qap = $this->qap->find($qap_id);
        if ($qap == null) {
            return response()->json(['message' => 'The question couldnt be found.'], 400);
        }
        return response()->json(['message' => 'Success', 'data' => $qap]);

    }

    public function postQuestionAndAnswer(Request $request) {
            $request->validate([
                'question' => 'required|unique:question_answer_pairs,question',
                'language_id' => 'required',
                'answer' => 'required',
            ]);

        if (!$this->validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers and Admins can submit here. If you are an admin please report this.'], 400);
        }
            $userobj = $request->user();
            $newQap = new QuestionAnswerPair;
            $newQap->question = $request->question;
            $newQap->tokens = $this->basicTokenizer($request->question);
            $newQap->language_id = $request->language_id;
            $newQap->answer = $request->answer;
            $newQap->user_asked_id = $userobj->id;
            $newQap->answerer_id = $userobj->id;
            $newQap->save();

            return response()->json(['message' => 'Success'], 200);
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
            $validatedData = $request->validate([
                'question' => 'required|string',
                'language_id' => 'required|numeric',
            ]);
            $userobj = $request->user();
            $newQap = new QuestionAnswerPair;
            $newQap->question = $request->question;
            $newQap->tokens = $this->basicTokenizer($request->question);
            $newQap->language_id = $request->language_id;
            $newQap->user_asked_id = $userobj->id;
            $newQap->save();
            return response()->json(['message' => 'Question saved successfully'], 201);
        }



    public function getAnswers(Request $request) {

        $faqPosts = $this->qap->all();
        $results = $faqPosts->where('answer', '!=', null);

        if (isset($request->language_id)) {
            $results = $results->where('language_id', $request->language_id);
        }

        if (isset($request->topic_id)) {
            $results = $results->where('topic_id', $request->topic_id);
        }

        $output = $this->serialize($results);

        // search now being done on frontend

        // if (isset($request->searchtokens)) {
        //     $jsonString = str_replace("'", '"', $request->searchtokens);
        //     $queryArray = json_decode($jsonString, false);
        //     $results = $this->tokenSort($queryArray, $results->toArray()); //token sort is below
        //     $output = $this->arrSerialize($results);
        // }

        return response()->json(["Message" => "Successfully retrieved", "data" => $output], 200);
    }

   private function getLanguageNameFromId($id) {

        return $this->language->find($id)->name ?? "Unknown";
    }

    private function arrSerialize($arr_of_answers) {
        $serialized = [];
        foreach ($arr_of_answers as $answer) {
            $serialized[] = [
                "question" => $answer['post']['question'],
                "answer" => $answer['post']['answer'],
                "answerer_id" => $answer['post']['answerer_id'],
                "language" => $this->getLanguageNameFromId($answer['post']['language_id']),
            ];
        }
        return $serialized;
    }

    private function serialize($arr_of_qaps) {
        $serialized = [];

        foreach ($arr_of_qaps as $qap) {

            $serialized[] = [
                "question" => $qap->question,
                "answer" => $qap->answer,
                "answerer_id" => $qap->answerer_id,
                "language" => $this->getLanguageNameFromId($qap->language_id),
                "tokens" => $qap->tokens,
            ];
        }
        return $serialized;
    }

    private function tokenSort(array $queryArray, array $resultsArray) : array {
        // when indexing into
         $rankedPosts = [];
         foreach ($resultsArray as $qap) {
            $question_tokens = explode(" ", $qap['tokens']);
            // unset($question_tokens[count($question_tokens) - 1]);
            $matches = array_intersect($queryArray, $question_tokens);
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
