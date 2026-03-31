<?php

namespace App\Http\Controllers\Frontend;

use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::published()
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search,   fn($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('excerpt', 'like', "%{$request->search}%");
            }))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 9);

        return response()->json([
            'status' => true,
            'data'   => $posts->items(),
            'meta'   => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'total'        => $posts->total(),
                'per_page'     => $posts->perPage(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();
        $post->increment('views');

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->category, fn($q) => $q->where('category', $post->category))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $comments = BlogComment::where('blog_post_id', $post->id)
            ->where('is_approved', true)
            ->latest()
            ->get(['id', 'name', 'comment', 'created_at']);

        return response()->json([
            'status'   => true,
            'data'     => $post,
            'related'  => $related,
            'comments' => $comments,
        ]);
    }

    public function storeComment(Request $request, string $slug)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:200',
            'comment' => 'required|string|max:2000',
        ]);

        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        BlogComment::create([
            'blog_post_id' => $post->id,
            'name'         => strip_tags($request->name),
            'email'        => $request->email,
            'comment'      => strip_tags($request->comment),
            'is_approved'  => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Comment submitted and awaiting moderation.',
        ]);
    }

    public function categories()
    {
        $categories = BlogPost::published()
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->pluck('category');

        return response()->json(['status' => true, 'data' => $categories]);
    }
}
