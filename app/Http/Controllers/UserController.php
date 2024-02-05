<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Hashing\HashManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
class UserController extends Controller
{
    private User $user;
    private HashManager $hasher;
    public function __construct(User $user, HashManager $hash) {
        $this->user = $user;
        $this->hasher = $hash;
    }


    private function hashPassword($password){
        return bcrypt($password);
    }

    public function signUp(Request $request) {
        try {
            $request->validate([
                'first_name'=> 'required|max:100',
                'last_name'=> 'required|max:100',
                'password' => 'required|min:5',
                'trainer'=> 'required|int|min:0|max:1',
                'superadmin'=>'required|int|min:0|max:1',
                'email'=>'required|email|unique:users,email',
                'profile_picture'=> 'max:100000',
            ]);

            $newUser = new User();
            $newUser->first_name = $request->first_name;
            $newUser->last_name = $request->last_name;
            $newUser->trainer = $request->trainer;
            $newUser->superadmin = $request->superadmin;
            $newUser->email = $request->email;
            $newUser->password = $this->hasher->make($request->password);
            if ($request->profile_picture) {
                $newUser->profile_picture = $request->profile_picture;
            }
            $newUser->save();

            return response()->json(["message" => "User Succesfully Created"], 200);

        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function logIn(Request $request) {
        if (isset($request->authkey)) {
            // return the call to auth key validator
        }
        try {
        $request->validate([
            'email'=> 'required|email',
            'password' => 'required|min:5',
        ]);
        $user = $this->user->where('email', $request->email)->firstOrFail();
        if ($user && $this->hasher->check($request->input('password'), $user->password))
        {
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json(['token' => $token]);
        }
    } catch (ModelNotFoundException $exception) {
        return response()->json(['message' => 'The provided credentials are incorrect.'], 422);
    }
        return response()->json(['message' => 'The provided credentials are incorrect.'], 422);
    }

    public function authError() {
        return response()->json(['message' => 'User is not logged in'], 422);
    }
}
