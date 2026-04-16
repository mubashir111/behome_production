<?php

namespace App\Services;

use Exception;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SupplierRequest;
use App\Libraries\QueryExceptionLibrary;

class SupplierService
{
    public object $user;
    public object $supplier;
    public array $supplierFilter = ['name', 'email', 'phone'];


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
            $orderType   = $request->get('order_type') ?? 'desc';

            return Supplier::with('media')->where(
                function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->supplierFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            )->orderBy($orderColumn, $orderType)->$method(
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
    public function store(SupplierRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $this->supplier = Supplier::create($request->validated());
                if ($request->image) {
                    $this->supplier->addMediaFromRequest('image')->toMediaCollection('supplier');
                }
                \App\Models\AdminNotification::record('info', 'Supplier Added', "New supplier '{$this->supplier->name}' ({$this->supplier->company}) was added by " . (auth()->user()->name ?? 'Admin'));
            });
            return $this->supplier;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(SupplierRequest $request, Supplier $supplier): object
    {
        try {
            DB::transaction(function () use ($supplier, $request) {
                $oldName = $supplier->name;
                $supplier->update($request->validated());
                $this->supplier = $supplier;

                if ($request->image) {
                    $this->supplier->clearMediaCollection('supplier');
                    $this->supplier->addMediaFromRequest('image')->toMediaCollection('supplier');
                }
                
                \App\Models\AdminNotification::record('info', 'Supplier Updated', "Supplier '{$oldName}' was updated by " . (auth()->user()->name ?? 'Admin'));
            });
            return $this->supplier;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Supplier $supplier): Supplier
    {
        try {
            return $supplier;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */

    public function destroy(Supplier $supplier): void
    {
        try {
            DB::transaction(function () use ($supplier) {
                // Safeguard: Check for active stock or purchases
                if ($supplier->purchases()->exists() || \App\Models\Stock::where('supplier_id', $supplier->id)->exists()) {
                    throw new Exception('Cannot delete supplier: They have existing purchase history or active stock records.', 422);
                }

                $name = $supplier->name;
                $company = $supplier->company;
                $supplier->delete();
                
                \App\Models\AdminNotification::record('warning', 'Supplier Removed', "Supplier '{$name}' ({$company}) was permanently removed by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info("Supplier deletion error: " . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}