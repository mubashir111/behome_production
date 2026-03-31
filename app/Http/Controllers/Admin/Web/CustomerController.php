<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Http\Requests\PaginateRequest;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $query = \App\Models\User::withCount('orders')
            ->role(\App\Enums\Role::CUSTOMER)
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(\App\Http\Requests\CustomerRequest $request)
    {
        try {
            $this->customerService->store($request);
            return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(User $customer)
    {
        $customer = $this->customerService->show($customer);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(User $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(\App\Http\Requests\CustomerRequest $request, User $customer)
    {
        try {
            $this->customerService->update($request, $customer);
            return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $customer)
    {
        try {
            $this->customerService->destroy($customer);
            return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
