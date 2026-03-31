<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use App\Models\StaticPage;

class StaticPageController extends Controller
{
    public function show(string $slug)
    {
        $page = StaticPage::where('slug', $slug)->where('is_active', true)->first();

        if (!$page) {
            return response()->json(['status' => false, 'message' => 'Page not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'slug'             => $page->slug,
                'title'            => $page->title,
                'content'          => $page->content,
                'sections'         => $page->sections ?? [],
                'meta_title'       => $page->meta_title,
                'meta_description' => $page->meta_description,
            ],
        ]);
    }

    public function faqs()
    {
        $faqs = FaqItem::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get(['id', 'category', 'question', 'answer']);

        $grouped = $faqs->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'label'    => FaqItem::$categories[$category] ?? ucfirst($category),
                'items'    => $items->values(),
            ];
        })->values();

        return response()->json(['status' => true, 'data' => $grouped]);
    }
}
