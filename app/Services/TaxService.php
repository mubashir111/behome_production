<?php

namespace App\Services;


use Exception;
use App\Models\Tax;
use App\Http\Requests\TaxRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;

class TaxService
{
    protected array $taxFilter = [
        'name',
        'code',
        'tax_rate',
        'type',
        'status'
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
            $orderType   = $request->get('order_type') ?? 'desc';

            return Tax::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->taxFilter)) {
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
    public function store(TaxRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $tax = Tax::create($request->validated());
                \App\Models\AdminNotification::record('info', 'Tax Created', "Financial tax '{$tax->name}' ({$tax->tax_rate}%) was created by " . (auth()->user()->name ?? 'Admin'));
                return $tax;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function update(TaxRequest $request, Tax $tax)
    {
        return DB::transaction(function () use ($request, $tax) {
            try {
                $oldName = $tax->name;
                $tax->update($request->validated());
                \App\Models\AdminNotification::record('info', 'Tax Updated', "Tax '{$oldName}' was updated to '{$tax->name}' by " . (auth()->user()->name ?? 'Admin'));
                return $tax;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function destroy(Tax $tax): void
    {
        try {
            DB::transaction(function () use ($tax) {
                // Safeguard: Check if used in products or stock
                if ($tax->products()->exists() || \App\Models\StockTax::where('tax_id', $tax->id)->exists()) {
                    throw new Exception('Cannot delete tax: It is currently applied to products or existing stock records.', 422);
                }

                $name = $tax->name;
                $rate = $tax->tax_rate;
                $tax->delete();

                \App\Models\AdminNotification::record('warning', 'Tax Deleted', "Tax '{$name}' ({$rate}%) was deleted by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info("Tax deletion error: " . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Tax $tax): Tax
    {
        try {
            return $tax;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
