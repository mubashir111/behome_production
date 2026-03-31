<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdministratorRequest;
use App\Http\Requests\PaginateRequest;
use App\Services\AdministratorService;
use App\Services\RoleService;
use App\Models\User;

class UsersController extends Controller
{
    public function index(PaginateRequest $request, AdministratorService $service)
    {
        $request->merge(['paginate' => 1]);
        $admins = $service->list($request);
        return view('admin.users.index', compact('admins'));
    }

    public function create(RoleService $roleService)
    {
        $roles = $roleService->list(new PaginateRequest(['paginate' => 0]));
        return view('admin.users.create', compact('roles'));
    }

    public function store(AdministratorRequest $request, AdministratorService $service)
    {
        try {
            $service->store($request);
            return redirect()->route('admin.users.index')->with('success', 'Administrator created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(User $user, RoleService $roleService)
    {
        $roles = $roleService->list(new PaginateRequest(['paginate' => 0]));
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(AdministratorRequest $request, User $user, AdministratorService $service)
    {
        try {
            $service->update($request, $user);
            return redirect()->route('admin.users.index')->with('success', 'Administrator updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $user, AdministratorService $service)
    {
        try {
            $service->destroy($user);
            return back()->with('success', 'Administrator deleted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
