<?php

namespace App\Services;

use App\Enums\Role as EnumsRole;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\RoleRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleService
{
    protected array $roleFilter = [
        'name'
    ];
    protected array $exceptFilter = [
        'excepts'
    ];
    protected array $roleArray = [
        EnumsRole::ADMIN, EnumsRole::CUSTOMER, EnumsRole::MANAGER, EnumsRole::POS_OPERATOR, EnumsRole::STUFF
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'asc';

            return Role::withCount('users')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->roleFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
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
    public function store(RoleRequest $request)
    {
        return DB::transaction(function() use ($request) {
            try {
                $role = Role::create($request->validated() + ['guard_name' => 'sanctum']);
                \App\Models\AdminNotification::record('info', 'Role Created', "New role '{$role->name}' was created by " . (auth()->user()->name ?? 'Admin'));
                return $role;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function update(RoleRequest $request, Role $role)
    {
        return DB::transaction(function() use ($request, $role) {
            try {
                $oldName = $role->name;
                $role->update($request->validated());
                \App\Models\AdminNotification::record('info', 'Role Updated', "Role '{$oldName}' was renamed/updated to '{$role->name}' by " . (auth()->user()->name ?? 'Admin'));
                return $role;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function destroy(Role $role): void
    {
        try {
            DB::transaction(function() use ($role) {
                if (!in_array($role->id, $this->roleArray)) {
                    $name = $role->name;
                    $role->delete();
                    \App\Models\AdminNotification::record('warning', 'Role Deleted', "Role '{$name}' was deleted by " . (auth()->user()->name ?? 'Admin'));
                } else {
                    throw new Exception("This role not deletable (System Protected Role)", 422);
                }
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Role $role): Role
    {
        try {
            return $role;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
