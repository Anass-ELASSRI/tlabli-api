<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Craftsman;
use App\Models\User;
use App\Services\CraftsmanService;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CraftsmanController extends Controller
{
    public function index()
    {
        $craftmen = User::craftmen()->get();
        return ApiResponse::success($craftmen, 'Craftsman retrieved successfully', 200);
    }

    public function completeRegistration(CraftsmanService $CraftsmanService, Request $request)
    {
        $response = $CraftsmanService->handleProfileStep($request);

        return $response;
    }


    public function show($id, Request $request)
    {
        $craftsman = Craftsman::find($id)->with('user')->first();

        if (!$craftsman) {
            return response()->json([
                'message' => 'Craftsman not found',
                'success' => false,
            ], 404);
        }
        $craftsman['is_owner'] = false;
        $accessToken = $request->bearerToken();
        if ($accessToken) {
            $token = PersonalAccessToken::findToken($accessToken);
            if ($token && $token->tokenable_id === $craftsman->user_id) {
                $craftsman['is_owner'] = true;
            }
        }

        return ApiResponse::success($craftsman, 'Craftsman retrieved successfully', 200);
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();


        $craftsman = User::craftmen()->find($id);

        if (!$craftsman) {
            return response()->json([
                'message' => 'Craftsman not found',
                'success' => false,
            ], 404);
        }
        if ($user != $craftsman->user->id) {
            ApiResponse::error('Unauthorized action.', 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|unique:users,' . $id . '|max:15',
        ]);

        $craftsman->update($data);

        return ApiResponse::success($craftsman, 'Craftsman updated successfully', 200);
    }
}
