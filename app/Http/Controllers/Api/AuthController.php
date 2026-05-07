<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'full_name' => $request->string('full_name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'password' => Hash::make($request->string('password')),
            'role' => UserRole::PU,
            'is_active' => true,
        ]);

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->success($this->presentUser($user->fresh('businessProfile')), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::guard('web')->attempt($credentials)) {
            return $this->error('INVALID_CREDENTIALS', 'Email atau password salah.', 401);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $user->is_active) {
            Auth::guard('web')->logout();

            return $this->error('ACCOUNT_INACTIVE', 'Akun Anda non-aktif.', 403);
        }

        if ($user->role !== UserRole::PU) {
            Auth::guard('web')->logout();

            return $this->error('FORBIDDEN', 'Akun internal harus login melalui /admin.', 403);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->success($this->presentUser($user->fresh('businessProfile')));
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->success(null, 204);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success($this->presentUser($user->load('businessProfile')));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->string('password')),
        ]);

        return $this->success(null, 204);
    }

    private function presentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
            'business_profile' => $user->businessProfile,
            'created_at' => $user->created_at,
        ];
    }
}
