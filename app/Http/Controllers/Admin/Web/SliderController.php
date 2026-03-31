<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::with('media')->latest()->paginate(12);
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:190'],
            'description' => ['nullable'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'badge_text'  => ['nullable', 'string', 'max:100'],
            'link'        => ['nullable', 'string', 'max:500'],
            'status'      => ['required', 'numeric'],
            'image'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $slider = Slider::create([
            'title'       => $request->title,
            'description' => $request->description,
            'button_text' => $request->button_text ?? 'Shop Now',
            'badge_text'  => $request->badge_text,
            'link'        => $request->link ?? '/shop',
            'status'      => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $slider->addMedia($request->file('image'))->toMediaCollection('slider');
        }

        return redirect()->route('admin.sliders.index')->with('success', 'Slider created successfully.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:190'],
            'description' => ['nullable'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'badge_text'  => ['nullable', 'string', 'max:100'],
            'link'        => ['nullable', 'string', 'max:500'],
            'status'      => ['required', 'numeric'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $slider->update([
            'title'       => $request->title,
            'description' => $request->description,
            'button_text' => $request->button_text ?? 'Shop Now',
            'badge_text'  => $request->badge_text,
            'link'        => $request->link ?? '/shop',
            'status'      => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $slider->clearMediaCollection('slider');
            $slider->addMedia($request->file('image'))->toMediaCollection('slider');
        }

        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        $slider->clearMediaCollection('slider');
        $slider->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.sliders.index')->with('success', 'Slider deleted successfully.');
    }

    public function toggleStatus(Slider $slider)
    {
        $slider->update([
            'status' => $slider->status == 5 ? 0 : 5,
        ]);
        return redirect()->route('admin.sliders.index')->with('success', 'Slider status updated.');
    }
}
