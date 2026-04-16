<?php

namespace App\Services;

use Exception;
use App\Enums\Ask;
use App\Models\User;
use App\Enums\Role as EnumRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ChangeImageRequest;
use App\Http\Requests\UserChangePasswordRequest;
use App\Libraries\QueryExceptionLibrary;


class CustomerService
{
    public object $user;
    public array $phoneFilter = ['phone'];
    public array $roleFilter = ['role_id'];
    public array $userFilter = ['name', 'email', 'username', 'status', 'phone'];
    public array $blockRoles = [EnumRole::ADMIN];


    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType = $request->get('order_type') ?? 'desc';

            return User::with('media', 'addresses')->role(EnumRole::CUSTOMER)->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->userFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                    $methodValue
                );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(CustomerRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $this->user = User::create([
                    'name'              => $request->name,
                    'email'             => $request->email,
                    'phone'             => $request->phone,
                    'username'          => $this->username($request->email),
                    'password'          => bcrypt($request->password),
                    'email_verified_at' => now(),
                    'status'            => $request->status,
                    'country_code'      => $request->country_code,
                    'is_guest'          => Ask::NO,
                ]);
                $this->user->assignRole(EnumRole::CUSTOMER);
                
                \App\Models\AdminNotification::record('info', 'Customer Registered', "Customer '{$this->user->name}' was registered by " . (auth()->user()->name ?? 'Admin'));
                
                return $this->user;
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(CustomerRequest $request, User $customer)
    {
        try {
            if (!in_array(EnumRole::CUSTOMER, $this->blockRoles)) {
                return DB::transaction(function () use ($customer, $request) {
                    $oldName = $customer->name;
                    $customer->update($request->validated());
                    
                    if ($request->password) {
                        $customer->password = Hash::make($request->password);
                        $customer->save();
                    }
                    
                    \App\Models\AdminNotification::record('info', 'Customer Updated', "Customer profile for '{$oldName}' was updated by " . (auth()->user()->name ?? 'Admin'));
                    
                    return $customer;
                });
            } else {
                throw new Exception(trans('all.message.permission_denied'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(User $customer): User
    {
        try {
            if (!in_array(EnumRole::CUSTOMER, $this->blockRoles)) {
                return $customer;
            } else {
                throw new Exception(trans('all.message.permission_denied'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(User $customer)
    {
        try {
            // ID 2 is the seeded root super-admin account — never allow it to be deleted here.
            if (!in_array(EnumRole::CUSTOMER, $this->blockRoles) && $customer->id !== 2) {
                if ($customer->hasRole(EnumRole::CUSTOMER)) {
                    return DB::transaction(function () use ($customer) {
                        // Safeguard: Check for active orders or wallet balance
                        if ($customer->orders()->exists()) {
                            throw new Exception('Cannot delete customer: They have an existing order history. Consider deactivating them instead.', 422);
                        }

                        $name = $customer->name;
                        $customer->addresses()->delete();
                        $customer->delete();
                        
                        \App\Models\AdminNotification::record('warning', 'Customer Removed', "Customer '{$name}' was permanently removed by " . (auth()->user()->name ?? 'Admin'));
                    });
                } else {
                    throw new Exception(trans('all.message.permission_denied'), 422);
                }
            } else {
                throw new Exception(trans('all.message.permission_denied'), 422);
            }
        } catch (Exception $exception) {
            Log::info("Customer deletion error: " . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function username($email): string
    {
        $emails = explode('@', $email);
        return $emails[0] . mt_rand();
    }

    /**
     * @throws Exception
     */
    public function changePassword(UserChangePasswordRequest $request, User $customer): User
    {
        try {
            if (!in_array(EnumRole::CUSTOMER, $this->blockRoles)) {
                $customer->password = Hash::make($request->password);
                $customer->save();
                return $customer;
            } else {
                throw new Exception(trans('all.message.permission_denied'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeImage(ChangeImageRequest $request, User $customer): User
    {
        try {
            if (!in_array(EnumRole::CUSTOMER, $this->blockRoles)) {
                if ($request->image) {
                    $customer->clearMediaCollection('profile');
                    $customer->addMediaFromRequest('image')->toMediaCollection('profile');
                }
                return $customer;
            } else {
                throw new Exception(trans('all.message.permission_denied'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
