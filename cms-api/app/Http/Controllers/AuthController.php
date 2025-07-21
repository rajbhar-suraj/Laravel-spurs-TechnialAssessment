<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// ✅ Required for Hash::check()

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // ✅ Validation
        $validated = $request->validate([
            'email'    => 'required|email|max:255',
            'password' => ['required', 'string', Password::min(8)],
        ]);

        $user = User::where('email', $validated['email'])
            ->select(['id', 'name', 'email', 'password'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], (string) $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->tokens()->exists()) {
            $user->tokens()->delete();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message'      => 'New session created (previous sessions terminated)',
                'access_token' => $token,
                'token_type'   => 'Bearer',
            ]);
        }
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out',
            ]);

        } catch (\Exception $e) {
            \Log::warning('Logout cleanup failed - token was already invalidated', [
                'user_id' => $request->user()->id ?? null,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Logout completed (session terminated)',
            ]);
        }
    }

}
