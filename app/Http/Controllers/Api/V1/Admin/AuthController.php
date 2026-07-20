<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->is_active) {
            throw ValidationException::withMessages(['email' => ['Akun tidak ditemukan atau tidak aktif.']]);
        }

        // Check brute force lock
        if ($user->locked_until && now()->lt($user->locked_until)) {
            $minutes = now()->diffInMinutes($user->locked_until);
            throw ValidationException::withMessages(['email' => ["Akun dikunci sementara. Coba lagi dalam {$minutes} menit."]]);
        }

        if (!Hash::check($request->password, $user->password)) {
            $user->increment('failed_login_attempts');
            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(30)]);
            }
            throw ValidationException::withMessages(['email' => ['Email atau password salah.']]);
        }

        // Successful login
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
        ]);

        $this->auditLog->log('login', $user, [], [], $user->id);

        $token = $user->createToken('admin-token', ['*'], now()->addDays($request->remember ? 30 : 1))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $this->auditLog->log('logout');
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
}
