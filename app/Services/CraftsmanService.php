<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Craftman;
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
        $craftman = $user->craftman;
        if (!$craftman) {
            $validator = ApiResponse::validate($request->all(), [
                'profession'   => 'required|string',
                'skills'       => 'required|string',
                'city'         => 'required|string|max:255',
                'legal_status'    => 'required|in:0,1,2',
                'experience_years'    => 'required|integer|min:0|max:50',
            ]);
            $data = array_merge($validator, [
                'current_step' => Craftman::STEP_DOCS,
            ]);
            $craftman = $user->craftman()->create($data);
            return [
                'message' => 'Craftman created successfully.',
                'craftman' => $craftman,
                'next_step' => Craftman::STEP_DOCS,
            ];
        }

        if ($user->craftman && $user->craftman->current_step == Craftman::STEP_DOCS) {
            $craftman->update([
                'current_step' => Craftman::STEP_COMPLETE,
                'status' => Craftman::PROFILE_COMPLETE,
            ]);
            $user->update([
                'status' => User::STATUS_ACTIVE,
            ]);
            return [
                'message' => 'Cratfsman profile completed',
                'next_step' => null
            ];
        }

        if ($user->craftman && $user->craftman->current_step == Craftman::STEP_COMPLETE) {
            return [
                'message' => 'Craftman profile is already complete.',
                'next_step' => null
            ];
        }
    }
}
