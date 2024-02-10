<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;




 class PostAuthUserController extends Controller
{
    public function __construct() {
        $this->middleware('auth:sanctum');
    }




    public function validateTrainerOrAdminRoute(Request $request) {
    $user = $request->user();
    return response()->json(['message' => $user], 200);
    if ($user->trainer == 0 && $user->superadmin == 0) {
        return response()->json(['message' => 'Only trainers and Admins can access this page. If you are an admin please report this.'], 400);
    }
    return response()->json(['message' => 'Success'], 200);
    }
}
