<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqWebController extends Controller
{
    public function index()
    {
        $faqs       = FaqItem::orderBy('category')->orderBy('sort_order')->get();
        $categories = FaqItem::$categories;
        return view('admin.faq.index', compact('faqs', 'categories'));
    }

    public function create()
    {
        $categories = FaqItem::$categories;
        return view('admin.faq.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'   => 'required|string',
            'question'   => 'required|string',
            'answer'     => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        DB::transaction(function() use ($data, $request) {
            FaqItem::create([
                'category'   => $data['category'],
                'question'   => $data['question'],
                'answer'     => $data['answer'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active'  => $request->boolean('is_active', true),
            ]);

            \App\Models\AdminNotification::record('info', 'FAQ Item Created', "A new FAQ item was added by " . (auth()->user()->name ?? 'Admin'));
        });

        return redirect()->route('admin.faq.index')->with('success', 'FAQ item created.');
    }

    public function edit(FaqItem $faq)
    {
        $categories = FaqItem::$categories;
        return view('admin.faq.edit', compact('faq', 'categories'));
    }

    public function update(Request $request, FaqItem $faq)
    {
        $data = $request->validate([
            'category'   => 'required|string',
            'question'   => 'required|string',
            'answer'     => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        DB::transaction(function() use ($data, $request, $faq) {
            $faq->update([
                'category'   => $data['category'],
                'question'   => $data['question'],
                'answer'     => $data['answer'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active'  => $request->boolean('is_active'),
            ]);

            \App\Models\AdminNotification::record('info', 'FAQ Item Updated', "An FAQ item was modified by " . (auth()->user()->name ?? 'Admin'));
        });

        return redirect()->route('admin.faq.index')->with('success', 'FAQ item updated.');
    }

    public function destroy(FaqItem $faq)
    {
        DB::transaction(function() use ($faq) {
            $faq->delete();
            \App\Models\AdminNotification::record('warning', 'FAQ Item Deleted', "An FAQ item was removed by " . (auth()->user()->name ?? 'Admin'));
        });
        return redirect()->route('admin.faq.index')->with('success', 'FAQ item deleted.');
    }
}
