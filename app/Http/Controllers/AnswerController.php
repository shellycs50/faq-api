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

    public function getAnswers(Request $request) {

        // Retrieve FAQ posts from the database
        $faqPosts = $this->answer->all();

        $results = $faqPosts;
        if (isset($request->searchtokens)) {
            $queryJson = $request->searchtokens;
            $queryArray = json_decode($queryJson, true);
            $results = $this->tokenSort($queryArray);
        }

        if (isset($request->language_id)) {
            $results = $results->where('language_id', $request->language_id);
        }

        if (isset($request->topic_id)) {
            $results = $results->where('topic_id', $request->topic_id);
        }

        $output = $this->serialize($results);

        return response()->json(["Message" => "Successfully retrieved", "Data" => $output], 200);
    }

    private function serialize($arr_of_answers) {
        $serialized = [];

        foreach ($arr_of_answers as $answer) {
            $serialized[] = [
                "content" => $answer->content,
                "question_id" => $answer->question_id,
                "answerer_id" => $answer->answerer_id,
                "search_title" => $answer->search_title
            ];
        }
        return $serialized;
    }




    private function tokenSort($queryArray) {

        // Calculate relevance scores for each FAQ post
         $rankedPosts = [];
         foreach ($faqPosts as $post) {
             $postTokens = explode(" ", strtolower($post->title)); // Assuming title contains tokens
             $matches = array_intersect($userTokens, $postTokens);
             $score = count($matches); // Simple scoring based on token matches
             $rankedPosts[] = [
                 'post' => $post,
                 'score' => $score
             ];
         }

         // Sort FAQ posts based on relevance scores
         usort($rankedPosts, function ($a, $b) {
             return $b['score'] - $a['score']; // Sort in descending order of scores
         });

         // Organize FAQ posts into hierarchical groups (e.g., exact match, partial match)
         $exactMatch = [];
         $partialMatch = [];
         $relatedTopics = [];

         foreach ($rankedPosts as $rankedPost) {
             if ($rankedPost['score'] == count($userTokens)) {
                 $exactMatch[] = $rankedPost['post'];
             } elseif ($rankedPost['score'] > 0) {
                 $partialMatch[] = $rankedPost['post'];
             } else {
                 $relatedTopics[] = $rankedPost['post'];
             }
         }

         // Combine hierarchical groups into a single result array
         $sortedResults = array_merge($exactMatch, $partialMatch, $relatedTopics);
    }
}
