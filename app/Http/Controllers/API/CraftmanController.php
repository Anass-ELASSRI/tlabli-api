<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\User;
use App\Services\CraftsmanService;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CraftmanController extends Controller
{
    public function index()
    {
        $craftmen = User::craftmen()->get();
        return ApiResponse::success($craftmen, 'Craftman retrieved successfully', 200);
    }

    public function completeRegistration(CraftsmanService $CraftmanService, Request $request)
    {
        $response = $CraftmanService->handleProfileStep($request);

        return $response;
    }


    public function show($id, Request $request)
    {
        $craftman = Craftman::find($id)->with('user')->first();

        if (!$craftman) {
            return response()->json([
                'message' => 'Craftman not found',
                'success' => false,
            ], 404);
        }
        $craftman['is_owner'] = false;
        $accessToken = $request->bearerToken();
        if ($accessToken) {
            $token = PersonalAccessToken::findToken($accessToken);
            if ($token && $token->tokenable_id === $craftman->user_id) {
                $craftman['is_owner'] = true;
            }
        }

        return ApiResponse::success($craftman, 'Craftman retrieved successfully', 200);
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();


        $craftman = User::craftmen()->find($id);

        if (!$craftman) {
            return response()->json([
                'message' => 'Craftman not found',
                'success' => false,
            ], 404);
        }
        if ($user != $craftman->user->id) {
            ApiResponse::error('Unauthorized action.', 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|unique:users,' . $id . '|max:15',
        ]);

        $craftman->update($data);

        return ApiResponse::success($craftman, 'Craftman updated successfully', 200);
    }
}
