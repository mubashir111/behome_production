<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\Http\Request;

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

        FaqItem::create([
            'category'   => $data['category'],
            'question'   => $data['question'],
            'answer'     => $data['answer'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active'  => $request->boolean('is_active', true),
        ]);

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

        $faq->update([
            'category'   => $data['category'],
            'question'   => $data['question'],
            'answer'     => $data['answer'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.faq.index')->with('success', 'FAQ item updated.');
    }

    public function destroy(FaqItem $faq)
    {
        $faq->delete();
        return redirect()->route('admin.faq.index')->with('success', 'FAQ item deleted.');
    }
}
