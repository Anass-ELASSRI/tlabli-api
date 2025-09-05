<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArtisanResource;
use App\Models\Artisan;
use App\Models\User;
use App\Services\ArtisanService;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ArtisanController extends Controller
{
    public function index()
    {
        $artisans = ArtisanResource::collection(Artisan::all());
        return ApiResponse::success($artisans, 'Artisan retrieved successfully', 200);
    }

    public function completeRegistration(ArtisanService $ArtisanService, Request $request)
    {
        $response = $ArtisanService->handleProfileStep($request);

        return $response;
    }


    public function show($id, Request $request)
    {
        $artisan = Artisan::find($id);

        if (!$artisan) {
            return response()->json([
                'message' => 'Artisan not found',
                'success' => false,
            ], 404);
        }
        // $artisan['is_owner'] = false;
        // $accessToken = $request->bearerToken();
        // if ($accessToken) {
        //     $token = PersonalAccessToken::findToken($accessToken);
        //     if ($token && $token->tokenable_id === $artisan->user_id) {
        //         $artisan['is_owner'] = true;
        //     }
        // }
        $artisan = new ArtisanResource($artisan);
        return ApiResponse::success($artisan, 'Artisan retrieved successfully', 200);
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();


        $artisan = User::craftmen()->find($id);

        if (!$artisan) {
            return response()->json([
                'message' => 'Artisan not found',
                'success' => false,
            ], 404);
        }
        if ($user != $artisan->user->id) {
            ApiResponse::error('Unauthorized action.', 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|unique:users,' . $id . '|max:15',
        ]);

        $artisan->update($data);

        return ApiResponse::success($artisan, 'Artisan updated successfully', 200);
    }

    public function test(Request $request)
    {
        $cookies = $request->cookies->all();
        $header = $request->header();
        return ApiResponse::success(['cookies' => $cookies, 'header' => $header], 'Test successful', 200);
    }
}
