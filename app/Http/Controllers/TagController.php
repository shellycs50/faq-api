<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Language;
use App\Models\Topic;

class TagController extends Controller
{
    private Language $language;
    private Topic $topic;

    public function __construct(Language $language, Topic $topic) {
        $this->language = $language;
        $this->topic = $topic;
        $this->middleware('auth:sanctum');
    }
//privates
    private function validateTrainerOrAdmin(Request $request) {
        $user = $request->user();
        if ($user->trainer == 0 || $user->superadmin == 0) {
            return false;
        }
        return true;
    }
//posts
    public function addLanguage(Request $request) {
        if (!validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers can add languages at the moment!']);
        }

        try {

            $request->validate([
                'name' => 'required|string',
            ]);

            $newLang = new Language;
            $newLang->name = $request->name;
            $newLang->save();

            return response()->json(['message' => 'Success'], 200);
        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function addTopic(Request $request) {
        if (!validateTrainerOrAdmin($request)) {
            return response()->json(['message' => 'Only trainers can add topics at the moment!']);
        }

        try {

            $request->validate([
                'name' => 'required|string',
                'language_id' => 'required|exists:languages,id',
            ]);

            $newTopic = new Topic;
            $newTopic->name = $request->name;
            $newTopic->language_id = $request->language_id;
            $newTopic->save();

            return response()->json(['message' => 'Success'], 200);
        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
    }

    // gets

    public function getLanguages(Request $request) {
        $query = $this->language->query();
        $langs = $query->get();
        $output = [];
        foreach ($langs as $lang) {
            $output[] = [
                "name" => $lang->name,
                "id" => $lang->id,
            ];
        }
        return response()->json(['message' => 'Success', 'data' => $output], 200);
    }

    public function getTopics(Request $request) {
        try {
            $request->validate([
                'language_id' => 'required|exists:languages,id',
            ]);

            $query = $this->topic->where('language_id', $request->language_id);
            $topics = $query->get();
            if (count($topics) === 0) {
                return response()->json(['Empty' => 'Empty'], 404);
            }
            return response()->json(['message' => 'Success', 'data' => $topics], 200);

        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
    }


}
