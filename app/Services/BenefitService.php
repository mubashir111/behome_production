<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\BenefitRequest;
use App\Models\Benefit;

class BenefitService
{
    protected $productCateFilter = [
        'title',
        'description',
        'status',
    ];

    protected $exceptFilter = [
        'excepts'
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

            return Benefit::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->productCateFilter)) {
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
    public function store(BenefitRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $benefit = Benefit::create($request->validated());
                if ($request->hasFile('image')) {
                    $benefit->addMediaFromRequest('image')->toMediaCollection('benefit');
                }
                \App\Models\AdminNotification::record('info', 'Benefit Created', "Benefit '{$benefit->title}' was created by " . (auth()->user()->name ?? 'Admin'));
                return $benefit;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    /**
     * @throws Exception
     */
    public function update(BenefitRequest $request, Benefit $benefit): Benefit
    {
        return DB::transaction(function () use ($request, $benefit) {
            try {
                $benefit->update($request->validated());
                if ($request->hasFile('image')) {
                    $benefit->clearMediaCollection('benefit');
                    $benefit->addMediaFromRequest('image')->toMediaCollection('benefit');
                }
                \App\Models\AdminNotification::record('info', 'Benefit Updated', "Benefit '{$benefit->title}' (ID #{$benefit->id}) was updated by " . (auth()->user()->name ?? 'Admin'));
                return $benefit;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    /**
     * @throws Exception
     */
    public function destroy(Benefit $benefit)
    {
        try {
            DB::transaction(function() use ($benefit) {
                $title = $benefit->title;
                $id    = $benefit->id;
                
                $benefit->clearMediaCollection('benefit');
                $benefit->delete();
                
                \App\Models\AdminNotification::record('warning', 'Benefit Deleted', "Benefit '{$title}' (ID #{$id}) was deleted by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Benefit $Benefit)
    {
        try {
            return $Benefit;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}