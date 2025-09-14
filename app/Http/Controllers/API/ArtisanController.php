<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Helpers\ApiResponse;
use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArtisanResource;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Http\Request;

class ArtisanController extends Controller
{
    public function index()
    {
        $artisans = ArtisanResource::collection(Artisan::all());
        return ApiResponse::success($artisans, 'Artisan retrieved successfully', 200);
    }

    public function completeRegistration(Request $request)
    {
        $NOT_ALLOWED_STATUS = [UserStatus::NotVerified, UserStatus::ProfilePending];

        $user = $request->user();
        $artisan = $user->artisan;
        if ($user->role != UserRoles::Artisan || !$artisan) {
            return ApiResponse::error('Unauthorized action.', 401);
        }
        if ($user->status == UserStatus::Active) {
            return ApiResponse::error('Artisan profile is already complete', 403);
        }
        if (in_array($user->status, $NOT_ALLOWED_STATUS)) {
            return ApiResponse::error('Unauthorized action.', 403);
        }
        $data = ApiResponse::validate($request->all(), [
            'experience_years' => 'required|integer|min:1',
            'location' => 'nullable|string|min:4|max:150',
            'certifications' => 'nullable|array',
            'certifications.*.title' => 'required_with:certifications.*.file|string|max:255',
            'certifications.*.file'  => 'required_with:certifications.*.title|file|mimes:pdf,jpg,png|max:2048',
            'profilePic' => 'nullable|image',  // image
            'social_links'     => 'nullable|array',
            'social_links.whatsapp' => 'required|string|regex:/^[0-9]+$/',
            // 'social_links.facebook' => 'nullable|url',
            // 'social_links.instagram' => 'nullable|url',
        ]);

        $artisan->update($data);
        $user->update(['status' => UserStatus::ProfilePending]);

        $cookie = (new CookiesHelper())->generateCookie(
            'access_token',
            (new JWTHelper())->generateJwt($user, 300),
            60 * 24 * 7
        );

        return ApiResponse::success(null, 'profile completed')->withCookie($cookie);
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
        $currentLocale = app()->getLocale();
        $message = __('messages.test');
        return ApiResponse::success(['currentLocale' => $currentLocale, 'message' => $message], 'Test successful', 200);
    }
}
