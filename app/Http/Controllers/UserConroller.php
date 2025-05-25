<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserConroller extends Controller
{
     public function profile(Request $request)
    {
        // Assuming the user is authenticated and you want to return their profile
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
