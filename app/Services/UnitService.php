<?php

namespace App\Services;


use App\Libraries\QueryExceptionLibrary;
use Exception;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UnitRequest;

class UnitService
{
    public object $unit;
    protected array $unitFilter = [
        'name',
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

            return Unit::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->unitFilter)) {
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
    public function store(UnitRequest $request): object
    {
        return DB::transaction(function () use ($request) {
            try {
                $this->unit = Unit::create($request->validated());
                \App\Models\AdminNotification::record('info', 'Unit Created', "Measurement unit '{$this->unit->name}' was created by " . (auth()->user()->name ?? 'Admin'));
                return $this->unit;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function update(UnitRequest $request, Unit $unit): Unit
    {
        return DB::transaction(function () use ($request, $unit) {
            try {
                $oldName = $unit->name;
                $unit->update($request->validated());
                \App\Models\AdminNotification::record('info', 'Unit Updated', "Unit '{$oldName}' was updated to '{$unit->name}' by " . (auth()->user()->name ?? 'Admin'));
                return $unit;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function destroy(Unit $unit): void
    {
        try {
            DB::transaction(function () use ($unit) {
                // Safeguard: Check for active products
                if ($unit->products()->exists()) {
                    throw new Exception('Cannot delete unit: It is associated with active products. Please update the products first.', 422);
                }

                $name = $unit->name;
                $code = $unit->code;
                
                $unit->delete();
                
                \App\Models\AdminNotification::record('warning', 'Unit Deleted', "Measurement unit '{$name}' ({$code}) was deleted by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info("Unit deletion error: " . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Unit $unit): Unit
    {
        try {
            return $unit;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
