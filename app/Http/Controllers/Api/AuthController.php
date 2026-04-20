<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    use ApiResponse;

    // public function __construct(private TwoFactorService $twoFactor) {}

    /**
     * Login — kembalikan Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Email atau password salah.', 401);
        }

        if ($user->status !== 'active') {
            return $this->error('Akun Anda tidak aktif. Hubungi administrator.', 403);
        }

        // Jika 2FA aktif — kembalikan flag, Flutter harus kirim kode 2FA
        // if ($this->twoFactor->isEnabled($user)) {
        //     // Buat temporary token khusus untuk 2FA challenge
        //     $tempToken = $user->createToken('2fa-pending', ['2fa-pending'])->plainTextToken;

        //     return response()->json([
        //         'success'           => true,
        //         'two_factor_required' => true,
        //         'temp_token'        => $tempToken,
        //         'message'           => 'Verifikasi 2FA diperlukan.',
        //     ]);
        // }

        // Login normal
        $deviceName = $request->device_name ?? $request->userAgent() ?? 'Flutter App';
        $token      = $user->createToken($deviceName)->plainTextToken;

        $user->recordLogin($request->ip());
        AuditLog::record('login', 'Login via API');

        return $this->success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => new UserResource($user),
        ], 'Login berhasil.');
    }

    /**
     * Verifikasi kode 2FA dan tukar temp_token dengan token penuh.
     */
    // public function verifyTwoFactor(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'code' => ['required', 'string'],
    //     ]);

    //     $user   = $request->user();
    //     $secret = $this->twoFactor->getSecret($user);
    //     $code   = preg_replace('/\s+/', '', $request->code);

    //     $valid = $this->twoFactor->verify($secret, $code)
    //         || $this->twoFactor->verifyRecoveryCode($user, $code);

    //     if (! $valid) {
    //         return $this->error('Kode 2FA tidak valid.', 422);
    //     }

    //     // Hapus temp token, buat token penuh
    //     $user->currentAccessToken()->delete();
    //     $deviceName = $request->header('X-Device-Name', 'Flutter App');
    //     $token      = $user->createToken($deviceName)->plainTextToken;

    //     $user->recordLogin($request->ip());
    //     AuditLog::record('login', 'Login via API (2FA verified)');

    //     return $this->success([
    //         'token'      => $token,
    //         'token_type' => 'Bearer',
    //         'user'       => new UserResource($user),
    //     ], 'Login berhasil.');
    // }

    /**
     * Register akun baru.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', Password::defaults(), 'confirmed'],
            'currency'              => ['nullable', 'string', 'size:3'],
            'timezone'              => ['nullable', 'string', 'max:50'],
            'device_name'           => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'currency' => $data['currency'] ?? 'IDR',
            'timezone' => $data['timezone'] ?? 'Asia/Jakarta',
        ]);

        $deviceName = $data['device_name'] ?? 'Flutter App';
        $token      = $user->createToken($deviceName)->plainTextToken;

        return $this->created([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => new UserResource($user),
        ], 'Registrasi berhasil.');
    }

    /**
     * Logout — hapus token aktif.
     */
    public function logout(Request $request): JsonResponse
    {
        AuditLog::record('logout', 'Logout via API');
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil.');
    }

    /**
     * Logout dari semua perangkat.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->success(null, 'Logout dari semua perangkat berhasil.');
    }

    /**
     * Data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    /**
     * Update profil.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'timezone' => ['sometimes', 'string', 'max:50'],
        ]);

        $user->update($data);

        return $this->success(new UserResource($user->fresh()), 'Profil berhasil diperbarui.');
    }

    /**
     * Ganti password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success(null, 'Password berhasil diperbarui.');
    }

    /**
     * Daftar semua perangkat/token aktif.
     */
    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens->map(fn($t) => [
            'id'         => $t->id,
            'name'       => $t->name,
            'last_used'  => $t->last_used_at?->diffForHumans(),
            'created_at' => $t->created_at->toIso8601String(),
            'is_current' => $t->id === $request->user()->currentAccessToken()->id,
        ]);

        return $this->success($tokens);
    }

    /**
     * Cabut token perangkat tertentu.
     */
    public function revokeToken(Request $request, int $tokenId): JsonResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return $this->success(null, 'Perangkat berhasil dicabut aksesnya.');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(null, 'Email sudah terverifikasi.');
        }
        $request->user()->sendEmailVerificationNotification();
        return $this->success(null, 'Email verifikasi telah dikirim.');
    }

    public function emailVerificationStatus(Request $request): JsonResponse
    {
        return $this->success([
            'email_verified' => $request->user()->hasVerifiedEmail(),
        ]);
    }
}
