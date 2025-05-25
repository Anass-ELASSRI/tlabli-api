<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CraftmanController extends Controller
{
    public function index(Request $request)
    {
        $craftmen = User::craftmen()->get();
        return response()->json([
            'data' => $craftmen,
            'message' => 'Craftmen retrieved successfully',
            'success' => true,
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users|max:15',
        ]);

        $craftman = User::craftmen()->create($data);

        return response()->json([
            'data' => $craftman,
            'message' => 'Craftman created successfully',
            'success' => true,
        ], 201);
    }
    public function show($id)
    {
        $craftman = User::craftmen()->find($id);

        if (!$craftman) {
            return response()->json([
                'message' => 'Craftman not found',
                'success' => false,
            ], 404);
        }

        return response()->json([
            'data' => $craftman,
            'message' => 'Craftman retrieved successfully',
            'success' => true,
        ]);
    }
    public function update(Request $request, $id)
    {
        $craftman = User::craftmen()->find($id);

        if (!$craftman) {
            return response()->json([
                'message' => 'Craftman not found',
                'success' => false,
            ], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|unique:users,' . $id . '|max:15',
        ]);

        $craftman->update($data);

        return response()->json([
            'data' => $craftman,
            'message' => 'Craftman updated successfully',
            'success' => true,
        ]);
    }
}
