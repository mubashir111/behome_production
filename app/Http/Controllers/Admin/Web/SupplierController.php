<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Exception;

class SupplierController extends Controller
{
    private SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(PaginateRequest $request)
    {
        try {
            $request->merge(['paginate' => 1]);
            $suppliers = $this->supplierService->list($request);
            return view('admin.suppliers.index', compact('suppliers'));
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(SupplierRequest $request)
    {
        try {
            $this->supplierService->store($request);
            return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        try {
            $this->supplierService->update($request, $supplier);
            return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $this->supplierService->destroy($supplier);
            return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted successfully.');
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
