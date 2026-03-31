<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Status;
use App\Enums\Role as EnumRole;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Enums\Ask;
use Exception;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if ($user->status !== Status::ACTIVE) {
            return $this->errorResponse('User account is inactive', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ], 'Login successful');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', 422, $validator->errors());
        }

        try {
            $user = User::create([
                'name'              => $request->name,
                'username'          => Str::slug($request->name) . rand(1, 1000),
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
                'phone'             => $request->phone,
                'status'            => Status::ACTIVE,
                'is_guest'          => Ask::NO,
                'email_verified_at' => Carbon::now(),
            ]);

            $user->assignRole(EnumRole::CUSTOMER);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user,
            ], 'User registered successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->successResponse([], 'Logged out successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function me(Request $request)
    {
        return $this->successResponse($request->user(), 'User profile details');
    }
}
