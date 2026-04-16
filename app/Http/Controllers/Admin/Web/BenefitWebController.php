<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use Illuminate\Http\Request;

class BenefitWebController extends Controller
{
    public function index()
    {
        $benefits = Benefit::orderBy('sort')->paginate(20);
        return view('admin.benefits.index', compact('benefits'));
    }

    public function create()
    {
        return view('admin.benefits.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort'        => ['nullable', 'integer'],
            'status'      => ['required', 'numeric'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        $benefit = Benefit::create([
            'title'       => $request->title,
            'description' => $request->description,
            'sort'        => $request->sort ?? 0,
            'status'      => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $benefit->addMedia($request->file('image'))->toMediaCollection('benefit');
        }

        \App\Models\AdminNotification::record('info', 'Benefit Item Created', "A new benefit/ticker item '{$benefit->title}' was added by " . (auth()->user()->name ?? 'Admin'));

        return redirect()->route('admin.benefits.index')->with('success', 'Benefit/ticker item created successfully.');
    }

    public function edit(Benefit $benefit)
    {
        return view('admin.benefits.edit', compact('benefit'));
    }

    public function update(Request $request, Benefit $benefit)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort'        => ['nullable', 'integer'],
            'status'      => ['required', 'numeric'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        $benefit->update([
            'title'       => $request->title,
            'description' => $request->description,
            'sort'        => $request->sort ?? 0,
            'status'      => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $benefit->clearMediaCollection('benefit');
            $benefit->addMedia($request->file('image'))->toMediaCollection('benefit');
        }

        \App\Models\AdminNotification::record('info', 'Benefit Item Updated', "Benefit/ticker item '{$benefit->title}' was updated by " . (auth()->user()->name ?? 'Admin'));

        return redirect()->route('admin.benefits.index')->with('success', 'Benefit/ticker item updated successfully.');
    }

    public function destroy(Benefit $benefit)
    {
        $title = $benefit->title;
        $benefit->clearMediaCollection('benefit');
        $benefit->delete();

        \App\Models\AdminNotification::record('warning', 'Benefit Item Deleted', "Benefit/ticker item '{$title}' was removed by " . (auth()->user()->name ?? 'Admin'));

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.benefits.index')->with('success', 'Benefit deleted successfully.');
    }
}
