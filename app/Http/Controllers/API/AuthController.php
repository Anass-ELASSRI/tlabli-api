<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRoles;
use App\Helpers\ApiResponse;
use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserToken;
use App\Models\UserVerification;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class AuthController extends Controller
{

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated', 401);
        }
        return ApiResponse::success(
            $user,
            'User retrieved successfully'
        );
    }

    public function login(Request $request)
    {
        $fields = ApiResponse::validate($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error('Invalid credentials', 400);
        }
        $authService = new AuthService();
        $res = $authService->login($user, $request);

        return ApiResponse::success(['status' => $user->status->value], 'Login successful')->cookie($res['cookieRefresh'])
            ->cookie($res['cookieAccess']);
    }

    public function logout(Request $request)
    {
        $authService = new AuthService();
        $res = $authService->logout($request);

        return ApiResponse::success(null, 'Logged out')->cookie($res['forgotAccess'])
            ->cookie($res['forgotRefresh']);
    }


    public function register(Request $request)
    {
        $data = ApiResponse::validate($request->all(), [
            'full_name'     => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:15',
            'password' => 'required|min:8',
            'city' => 'required|string',
            'role' => ['required', new Enum(UserRoles::class)],
        ]);


        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        // Generate phone verification code
        $code = rand(100000, 999999);

        // 3️⃣ Store code in DB
        DB::transaction(function () use ($user, $code) {
            $user->verifications()->create([
                'type' => UserVerification::TYPE_PHONE,
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0,
            ]);
        });

        // 4️⃣ Send SMS via Textbelt


        // 5 login
        $authService = new AuthService();
        $res = $authService->login($user, $request);

        return ApiResponse::success(['user' => $user], 'Login successful')->cookie($res['cookieRefresh'])
            ->cookie($res['cookieAccess']);
    }
    public function registerArtisan(Request $request)
    {
        $userData = ApiResponse::validate($request->all(), [
            'full_name'     => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:15',
            'password' => 'required|min:8',
            'city' => 'required|string',
        ]);

        $artisanData = ApiResponse::validate($request->all(), [
            'profession' => 'required|string',
            'skills' => 'required|array'
        ]);



        $userData['role'] = UserRoles::Artisan->value;
        $userData['password'] = Hash::make($userData['password']);
        $user = User::create($userData);

        // create the artisan
        $user->artisan()->create($artisanData);

        // Generate phone verification code
        $code = rand(100000, 999999);

        // 3️⃣ Store code in DB
        DB::transaction(function () use ($user, $code) {
            $user->verifications()->create([
                'type' => UserVerification::TYPE_PHONE,
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0,
            ]);
        });

        // 5 login
        $authService = new AuthService();
        $res = $authService->login($user, $request);

        return ApiResponse::success(['user' => $user], 'Login successful')->cookie($res['cookieRefresh'])
            ->cookie($res['cookieAccess']);
    }
}
