<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionWebController extends Controller
{
    public function index()
    {
        $promotions = Promotion::latest()->paginate(12);
        return view('admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:190'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link'     => ['nullable', 'string', 'max:500'],
            'type'     => ['required', 'integer', 'in:1,5,10'],
            'status'   => ['required', 'numeric'],
            'image'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ]);

        $promotion = Promotion::create([
            'name'     => $request->name,
            'slug'     => Str::slug($request->name) . rand(100, 999),
            'subtitle' => $request->subtitle,
            'link'     => $request->link ?? '/shop',
            'type'     => $request->type,
            'status'   => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $promotion->addMedia($request->file('image'))->toMediaCollection('promotion');
        }

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created successfully.');
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:190'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link'     => ['nullable', 'string', 'max:500'],
            'type'     => ['required', 'integer', 'in:1,5,10'],
            'status'   => ['required', 'numeric'],
            'image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ]);

        $promotion->update([
            'name'     => $request->name,
            'subtitle' => $request->subtitle,
            'link'     => $request->link ?? '/shop',
            'type'     => $request->type,
            'status'   => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $promotion->clearMediaCollection('promotion');
            $promotion->addMedia($request->file('image'))->toMediaCollection('promotion');
        }

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->clearMediaCollection('promotion');
        $promotion->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion deleted successfully.');
    }
}
