<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRoles;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVerification;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class AuthController extends Controller
{

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ApiResponse::error(__('auth.unauthenticated'), 401);
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
            return ApiResponse::error(__('auth.invalid_credentials'), 400);
        }

        if ($user->isSuspended()) {
            return ApiResponse::error(__('auth.account.suspended'), 403);
        }

        $authService = new AuthService();
        $res = $authService->login($user, $request);

        return ApiResponse::success(['status' => $user->status->value], __('auth.login_successful'))->cookie($res['cookieRefresh'])
            ->cookie($res['cookieAccess']);
    }

    public function logout(Request $request)
    {
        $authService = new AuthService();
        $res = $authService->logout($request);

        return ApiResponse::success(null, __('auth.logged_out'))->cookie($res['forgotAccess'])
            ->cookie($res['forgotRefresh']);
    }


    public function register(Request $request)
    {
        $data = ApiResponse::validate($request->all(), [
            'first_name' => 'required|string|min:2|max:50',
            'last_name'  => 'required|string|min:2|max:50',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:15',
            'password' => 'required|min:8',
            'city' => 'required|string|max:50',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['full_name'] = $data['first_name'] . $data['last_name'];
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

        return ApiResponse::success(['user' => $user], __('auth.register_successful'))->cookie($res['cookieRefresh'])
            ->cookie($res['cookieAccess']);
    }
    public function registerArtisan(Request $request)
    {
        $validated = ApiResponse::validate($request->all(), [
            'first_name'  => 'required|string|min:2|max:50',
            'last_name'   => 'required|string|min:2|max:50',
            'email'       => 'nullable|email|unique:users,email',
            'phone'       => 'required|string|unique:users,phone|max:15',
            'password'    => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',
            'city'        => 'required|string',
            'profession'  => 'required|string',
        ]);

        // Prepare user data
        $userData = [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'full_name'  => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'email'      => $validated['email'] ?? null,
            'phone'      => $validated['phone'],
            'city'       => $validated['city'],
            'role'       => UserRoles::Artisan->value,
            'password'   => Hash::make($validated['password']),
        ];

        // Create user
        $user = User::create($userData);

        // Create artisan data
        $artisanData = [
            'profession' => $validated['profession'],
            'skills'     => $validated['skills'],
        ];

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

        return ApiResponse::success(['user' => $user], __('auth.register_successful'))->cookie($res['cookieRefresh'])
            ->cookie($res['cookieAccess']);
    }
}
