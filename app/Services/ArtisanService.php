<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Http\Request;

class ArtisanService
{
    public function handleProfileStep(Request $request)
    {
        $user = $request->user();
        if ($user->role != User::ROLE_CRAFTMAN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 422);
        }
        $artisan = $user->artisan;
        if (!$artisan) {
            $validator = ApiResponse::validate($request->all(), [
                'profession'   => 'required|string',
                'bio'          => 'nullable|string|max:1000',
                'phone'        => 'nullable|string|max:15',
                'skills'       => 'required|string',
                'city'         => 'required|string|max:255',
                'languages'         => 'required|string|max:255',
                'legal_status'    => 'required|in:0,1,2',
                'experience_years'    => 'required|integer|min:0|max:50',
            ]);
            $data = array_merge($validator, [
                'current_step' => Artisan::STEP_DOCS,
            ]);
            $artisan = $user->artisan()->create($data);
            return [
                'message' => 'Artisan created successfully.',
                'artisan' => $artisan,
                'next_step' => Artisan::STEP_DOCS,
            ];
        }

        if ($user->artisan && $user->artisan->current_step == Artisan::STEP_DOCS) {
            $artisan->update([
                'current_step' => Artisan::STEP_COMPLETE,
                'status' => Artisan::PROFILE_COMPLETE,
            ]);
            $user->update([
                'status' => User::STATUS_ACTIVE,
            ]);
            return [
                'message' => 'Cratfsman profile completed',
                'next_step' => null
            ];
        }

        if ($user->artisan && $user->artisan->current_step == Artisan::STEP_COMPLETE) {
            return [
                'message' => 'Artisan profile is already complete.',
                'next_step' => null
            ];
        }
    }

    public function handleRequest(Request $request,Artisan $artisan, User $client)
    {
        if ($client->role != User::ROLE_USER) {
            return ApiResponse::error('Unauthorized action.', 422);
        }

        if ($artisan->hasPendingRequestFrom($client)) {
            return ApiResponse::error('You already have a pending request for this artisan.', 422);
        }
        $data = ApiResponse::validate($request->all(), [
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $request = $artisan->requests()->create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
        ]);
        return ApiResponse::success($request, 'Artisan request created successfully', 201);
    }
}
