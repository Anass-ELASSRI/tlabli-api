<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Craftsman;
use App\Models\User;
use Illuminate\Http\Request;

class CraftsmanService
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
        $craftsman = $user->craftsman;
        if (!$craftsman) {
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
                'current_step' => Craftsman::STEP_DOCS,
            ]);
            $craftsman = $user->craftsman()->create($data);
            return [
                'message' => 'Craftsman created successfully.',
                'craftsman' => $craftsman,
                'next_step' => Craftsman::STEP_DOCS,
            ];
        }

        if ($user->craftsman && $user->craftsman->current_step == Craftsman::STEP_DOCS) {
            $craftsman->update([
                'current_step' => Craftsman::STEP_COMPLETE,
                'status' => Craftsman::PROFILE_COMPLETE,
            ]);
            $user->update([
                'status' => User::STATUS_ACTIVE,
            ]);
            return [
                'message' => 'Cratfsman profile completed',
                'next_step' => null
            ];
        }

        if ($user->craftsman && $user->craftsman->current_step == Craftsman::STEP_COMPLETE) {
            return [
                'message' => 'Craftsman profile is already complete.',
                'next_step' => null
            ];
        }
    }

    public function handleRequest(Request $request,Craftsman $craftsman, User $client)
    {
        if ($client->role != User::ROLE_USER) {
            return ApiResponse::error('Unauthorized action.', 422);
        }

        if ($craftsman->hasPendingRequestFrom($client)) {
            return ApiResponse::error('You already have a pending request for this craftsman.', 422);
        }
        $data = ApiResponse::validate($request->all(), [
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $request = $craftsman->requests()->create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
        ]);
        return ApiResponse::success($request, 'Craftsman request created successfully', 201);
    }
}
