<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Craftsman;
use App\Models\User;
use App\Services\CraftsmanService;
use Illuminate\Http\Request;

class CraftsmanRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $craftsman, CraftsmanService $craftsmanService)
    {
        $client = $request->user();
        $craftsman = Craftsman::find($craftsman);
        if (!$craftsman) {
            return ApiResponse::error('Craftsman not found', 404);
        }
        $response = $craftsmanService->handleRequest($request, $craftsman, $client);
        return $response;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
