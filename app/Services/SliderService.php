<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\SliderRequest;
use App\Models\Slider;

class SliderService
{
    protected $sliderFilter = [
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

            return Slider::with('media')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->sliderFilter)) {
                        if ($key === 'status') {
                            $query->where($key, $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
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
    public function store(SliderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $slider = Slider::create($request->validated() + ['link' => $request->link]);
                if ($request->hasFile('image')) {
                    $slider->addMediaFromRequest('image')->toMediaCollection('slider');
                }
                \App\Models\AdminNotification::record('info', 'Slider Created', "Slider '{$slider->title}' was created by " . (auth()->user()->name ?? 'Admin'));
                return $slider;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    /**
     * @throws Exception
     */
    public function update(SliderRequest $request, Slider $slider): Slider
    {
        return DB::transaction(function () use ($request, $slider) {
            try {
                $slider->update($request->validated() + ['link' => $request->link]);
                if ($request->hasFile('image')) {
                    $slider->clearMediaCollection('slider');
                    $slider->addMediaFromRequest('image')->toMediaCollection('slider');
                }
                \App\Models\AdminNotification::record('info', 'Slider Updated', "Slider '{$slider->title}' (ID #{$slider->id}) was updated by " . (auth()->user()->name ?? 'Admin'));
                return $slider;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    /**
     * @throws Exception
     */
    public function destroy(Slider $slider)
    {
        try {
            DB::transaction(function() use ($slider) {
                $title = $slider->title;
                $id    = $slider->id;
                
                $slider->clearMediaCollection('slider');
                $slider->delete();
                
                \App\Models\AdminNotification::record('warning', 'Slider Deleted', "Slider '{$title}' (ID #{$id}) was deleted by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Slider $slider): Slider
    {
        try {
            return $slider;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
