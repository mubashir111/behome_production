<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Ask;
use App\Enums\Role as EnumRole;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Verify a Google ID token sent from the frontend and return a Sanctum token.
     * The frontend uses Google's client-side SDK to get the credential (ID token),
     * then POSTs it here. We verify it with Google's tokeninfo endpoint.
     */
    public function handleToken(Request $request): JsonResponse
    {
        $request->validate(['credential' => 'required|string']);

        try {
            // Verify the ID token with Google
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $request->credential,
            ]);

            if (!$response->ok()) {
                return new JsonResponse(['status' => false, 'message' => 'Invalid Google token.'], 422);
            }

            $google = $response->json();

            // Verify the token was issued for our app
            $clientId = config('services.google.client_id');
            if ($clientId && ($google['aud'] ?? '') !== $clientId) {
                return new JsonResponse(['status' => false, 'message' => 'Token audience mismatch.'], 422);
            }

            $email    = $google['email'] ?? null;
            $name     = $google['name'] ?? ($google['given_name'] ?? 'User');
            $googleId = $google['sub'] ?? null;
            $avatar   = $google['picture'] ?? null;

            if (!$email) {
                return new JsonResponse(['status' => false, 'message' => 'Google account has no email.'], 422);
            }

            // Find or create user
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name'              => $name,
                    'username'          => Str::slug($name) . rand(100, 9999),
                    'email'             => $email,
                    'google_id'         => $googleId,
                    'avatar'            => $avatar,
                    'password'          => bcrypt(Str::random(24)),
                    'email_verified_at' => Carbon::now(),
                    'status'            => Status::ACTIVE,
                    'is_guest'          => Ask::NO,
                ]);
                $user->assignRole(EnumRole::CUSTOMER);
            } else {
                // Update google_id if not set
                if (!$user->google_id) {
                    $user->google_id = $googleId;
                    $user->save();
                }

                if ($user->status !== Status::ACTIVE) {
                    return new JsonResponse(['status' => false, 'message' => 'Your account is inactive.'], 403);
                }
            }

            $token = $user->createToken('google_auth')->plainTextToken;

            return new JsonResponse([
                'status'  => true,
                'message' => 'Login successful.',
                'data'    => [
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                    'user'         => [
                        'id'     => $user->id,
                        'name'   => $user->name,
                        'email'  => $user->email,
                        'avatar' => $user->avatar,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return new JsonResponse(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
