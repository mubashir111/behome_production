<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\PaginateRequest;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(PaginateRequest $request, RoleService $service)
    {
        $request->merge(['paginate' => 1]);
        $roles = $service->list($request);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(RoleRequest $request, RoleService $service)
    {
        try {
            $service->store($request);
            return redirect()->route('admin.roles.index')->with('success', 'Role created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(RoleRequest $request, Role $role, RoleService $service)
    {
        try {
            $service->update($request, $role);
            return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Role $role, RoleService $service)
    {
        try {
            $service->destroy($role);
            return back()->with('success', 'Role deleted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
