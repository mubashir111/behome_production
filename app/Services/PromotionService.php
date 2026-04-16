<?php

namespace App\Services;


use Exception;
use App\Models\Promotion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PromotionRequest;
use App\Http\Requests\ChangeImageRequest;

class PromotionService
{
    public object $promotion;
    protected array $promotionFilter = [
        'name',
        'subtitle',
    ];

    protected array $promotionExactFilter = [
        'type',
        'status',
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType = $request->get('order_type') ?? 'desc';

            return Promotion::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->promotionFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }

                    if (in_array($key, $this->promotionExactFilter)) {
                        $query->where($key, $request);
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
    public function store(PromotionRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $this->promotion = Promotion::create($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $this->promotion->addMedia($request->image)->toMediaCollection('promotion');
                }
                \App\Models\AdminNotification::record('info', 'Promotion Created', "Promotion '{$this->promotion->name}' was created by " . (auth()->user()->name ?? 'Admin'));
            });
            return $this->promotion;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());

            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(PromotionRequest $request, Promotion $promotion): Promotion
    {
        return DB::transaction(function () use ($request, $promotion) {
            try {
                $promotion->update($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $promotion->clearMediaCollection('promotion');
                    $promotion->addMedia($request->image)->toMediaCollection('promotion');
                }
                \App\Models\AdminNotification::record('info', 'Promotion Updated', "Promotion '{$promotion->name}' (ID #{$promotion->id}) was updated by " . (auth()->user()->name ?? 'Admin'));
                return $promotion;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    /**
     * @throws Exception
     */
    public function destroy(Promotion $promotion): void
    {
        try {
            DB::transaction(function() use ($promotion) {
                $name = $promotion->name;
                $id   = $promotion->id;

                $promotion->promotionProducts()->delete();
                $promotion->clearMediaCollection('promotion');
                $promotion->delete();

                \App\Models\AdminNotification::record('warning', 'Promotion Deleted', "Promotion '{$name}' (ID #{$id}) was deleted by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Promotion $promotion): Promotion
    {
        try {
            return $promotion;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeImage(ChangeImageRequest $request, Promotion $promotion): Promotion
    {
        try {
            if ($request->image) {
                $promotion->clearMediaCollection('promotion');
                $promotion->addMedia($request->image)->toMediaCollection('promotion');
            }
            return $promotion;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
