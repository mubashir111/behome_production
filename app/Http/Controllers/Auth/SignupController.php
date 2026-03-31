<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Enums\Ask;
use Carbon\Carbon;
use App\Models\User;
use App\Enums\Activity;
use Illuminate\Support\Str;
use App\Libraries\AppLibrary;
use App\Services\MenuService;
use App\Enums\Role as EnumRole;
use Illuminate\Http\JsonResponse;
use App\Services\OtpManagerService;
use App\Services\PermissionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\MenuResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Smartisan\Settings\Facades\Settings;
use App\Http\Requests\SignupEmailRequest;
use App\Http\Requests\SignupPhoneRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\VerifyPhoneRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;

class SignupController extends Controller
{

    private OtpManagerService $otpManagerService;
    public string $token;
    public PermissionService $permissionService;
    public MenuService $menuService;

    public function __construct(OtpManagerService $otpManagerService, PermissionService $permissionService, MenuService $menuService)
    {
        $this->otpManagerService = $otpManagerService;
        $this->permissionService = $permissionService;
        $this->menuService       = $menuService;
    }

    public function otpPhone(
        SignupPhoneRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->otpManagerService->otpPhone($request);
            return response(['status' => true, 'message' => trans("all.message.check_your_phone_for_code")]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function otpEmail(
        SignupEmailRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->otpManagerService->otpEmail($request);
            return response(['status' => true, 'message' => trans("all.message.check_your_email_for_code")]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function verifyPhone(
        VerifyPhoneRequest $request
    ): JsonResponse {
        try {
            $this->otpManagerService->verifyPhone($request);
            $user = User::where(['phone' => $request->phone, 'country_code' => $request->country_code])->first();
            if ($user) {
                Auth::guard('web')->loginUsingId($user->id);
                $this->token = $user->createToken('auth_token')->plainTextToken;
                $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
                $defaultPermission = AppLibrary::defaultPermission($permission);
                return new JsonResponse([
                    'status'            => true,
                    'message'           => trans("all.message.register_successfully"),
                    'token'             => $this->token,
                    'user'              => new UserResource($user),
                    'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
                    'permission'        => $permission,
                    'defaultPermission' => $defaultPermission,
                ], 201);
            }
            return new JsonResponse([
                'status'  => false,
                'message' => trans('all.message.code_is_invalid')
            ], 422);
        } catch (Exception $exception) {
            return new JsonResponse(['status'  => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function verifyEmail(
        VerifyEmailRequest $request
    ): JsonResponse {
        try {
            $this->otpManagerService->verifyEmail($request);
            $user = User::where(['email' => $request->email])->first();
            if ($user) {
                Auth::guard('web')->loginUsingId($user->id);
                $this->token = $user->createToken('auth_token')->plainTextToken;
                $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
                $defaultPermission = AppLibrary::defaultPermission($permission);
                return new JsonResponse([
                    'status'            => true,
                    'message'           => trans("all.message.register_successfully"),
                    'token'             => $this->token,
                    'user'              => new UserResource($user),
                    'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
                    'permission'        => $permission,
                    'defaultPermission' => $defaultPermission,
                ], 201);
            }
            return new JsonResponse([
                'status'  => false,
                'message' => trans('all.message.code_is_invalid'),
            ], 422);
        } catch (Exception $exception) {
            return new JsonResponse(['status'  => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function register(SignupRequest $request)
    {
        $user = User::create([
            'name'              => $request->post('name'),
            'username'          => Str::slug($request->post('name')) . rand(1, 500),
            'email'             => $request->post('email'),
            'phone'             => $request->post('phone'),
            'country_code'      => $request->post('country_code'),
            'email_verified_at' => Carbon::now()->getTimestamp(),
            'is_guest'          => Ask::NO,
            'password'          => Hash::make($request->post('password'))
        ]);
        $user->assignRole(EnumRole::CUSTOMER);
        if ($user) {
            if (Settings::group('site')->get('site_phone_verification') == Activity::ENABLE && $request->post('country_code') && $request->post('phone')) {
                $this->otpManagerService->otpPhone($request);
                return response(['status' => true, 'message' => trans("all.message.check_your_phone_for_code")]);
            } else if (Settings::group('site')->get('site_email_verification') == Activity::ENABLE && $request->post('email')) {
                $this->otpManagerService->otpEmail($request);
                return response(['status' => true, 'message' => trans('all.message.check_your_email_for_code')]);
            } else {
                return response(['status' => true, 'message' => trans('all.message.register_successfully')]);
            }
        } else {
            return response(['status' => false, 'message' => trans('all.message.register_not_completed')], 422);
        }
    }

    public function signupLoginVerify(Request $request)
    {
        try {
            $user = User::where(['phone' => $request->phone, 'country_code' => $request->country_code])->orWhere(['email' => $request->email])->first();
            if ($user) {
                Auth::guard('web')->loginUsingId($user->id);
                $this->token = $user->createToken('auth_token')->plainTextToken;
                $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
                $defaultPermission = AppLibrary::defaultPermission($permission);
                return new JsonResponse([
                    'status'            => true,
                    'message'           => trans('all.message.register_successfully'),
                    'token'             => $this->token,
                    'user'              => new UserResource($user),
                    'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
                    'permission'        => $permission,
                    'defaultPermission' => $defaultPermission,
                ], 201);
            } else {
                return response(['status' => false, 'message' => trans('all.message.register_not_completed')], 422);
            }
        } catch (Exception $exception) {
            return new JsonResponse(['status'  => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
