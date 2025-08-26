<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Models\User;
use App\Services\ArtisanService;
use Illuminate\Http\Request;

class ArtisanRequestController extends Controller
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
    public function store(Request $request, $artisan, ArtisanService $artisanService)
    {
        $client = $request->user();
        $artisan = Artisan::find($artisan);
        if (!$artisan) {
            return ApiResponse::error('Artisan not found', 404);
        }
        $response = $artisanService->handleRequest($request, $artisan, $client);
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
