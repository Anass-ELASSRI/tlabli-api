<?php

namespace App\Services;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Helpers\ApiResponse;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Http\Request;

class ArtisanService
{
    const NOT_ALLOWED_STATUS = [UserStatus::NotVerified, UserStatus::ProfilePending];
    public function handleProfileStep(Request $request)
    {
        $user = $request->user();
        $artisan = $user->artisan;
        if ($user->role != UserRoles::Artisan || !$artisan) {
            return ApiResponse::error('Unauthorized action.', 400);
        }
        if ($user->status == UserStatus::Active) {
            return ApiResponse::error('Artisan profile is already complete', 400);
        }
        if (in_array($user->status, $this::NOT_ALLOWED_STATUS)) {
            return ApiResponse::error('Unauthorized action.', 400);
        }
    }

    // public function handleRequest(Request $request, Artisan $artisan, User $client)
    // {
    //     if ($client->role != UserRoles::Client) {
    //         return ApiResponse::error('Unauthorized action', 422);
    //     }

    //     if ($artisan->hasPendingRequestFrom($client)) {
    //         return ApiResponse::error('You already have a pending request for this artisan.', 422);
    //     }
    //     $data = ApiResponse::validate($request->all(), [
    //         'subject' => 'nullable|string|max:255',
    //         'message' => 'required|string',
    //     ]);

    //     $request = $artisan->requests()->create([
    //         'user_id' => $request->user()->id,
    //         'subject' => $data['subject'] ?? null,
    //         'message' => $data['message'],
    //     ]);
    //     return ApiResponse::success($request, 'Artisan request created successfully', 201);
    // }
}
