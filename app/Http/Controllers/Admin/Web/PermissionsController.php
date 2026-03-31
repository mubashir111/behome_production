<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Services\PermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Libraries\AppLibrary;

class PermissionsController extends Controller
{
    public function edit(Role $role)
    {
        $permissions = Permission::get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $permissions = AppLibrary::permissionWithAccess($permissions, collect($rolePermissions));
        return view('admin.permissions.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(PermissionRequest $request, Role $role, PermissionService $service)
    {
        try {
            $service->update($request, $role);
            return redirect()->route('admin.roles.index')->with('success', 'Permissions updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
