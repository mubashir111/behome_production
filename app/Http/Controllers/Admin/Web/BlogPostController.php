<?php

namespace App\Http\Controllers\Admin\Web;

use App\Models\BlogPost;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::latest()->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'excerpt'          => ['nullable', 'string'],
            'content'          => ['nullable', 'string'],
            'cover_image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'category'         => ['nullable', 'string', 'max:100'],
            'author'           => ['nullable', 'string', 'max:100'],
            'is_published'     => ['nullable', 'boolean'],
            'published_at'     => ['nullable', 'date'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::slug($request->title);
        $base = $slug;
        $i    = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $data             = $request->only(['title', 'excerpt', 'content', 'category', 'author', 'meta_title', 'meta_description']);
        $data['slug']     = $slug;
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $request->published_at ?? ($data['is_published'] ? now() : null);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('blog', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully.');
    }

    public function edit(BlogPost $blog)
    {
        return view('admin.blog.edit', compact('blog'));
    }

    public function update(Request $request, BlogPost $blog)
    {
        $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'excerpt'          => ['nullable', 'string'],
            'content'          => ['nullable', 'string'],
            'cover_image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'category'         => ['nullable', 'string', 'max:100'],
            'author'           => ['nullable', 'string', 'max:100'],
            'is_published'     => ['nullable', 'boolean'],
            'published_at'     => ['nullable', 'date'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data             = $request->only(['title', 'excerpt', 'content', 'category', 'author', 'meta_title', 'meta_description', 'published_at']);
        $data['is_published'] = $request->boolean('is_published');

        if (!$data['published_at'] && $data['is_published'] && !$blog->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('blog', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }

        $blog->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted.');
    }

    public function togglePublish(BlogPost $blog)
    {
        $blog->update([
            'is_published' => !$blog->is_published,
            'published_at' => !$blog->is_published ? ($blog->published_at ?? now()) : $blog->published_at,
        ]);
        return redirect()->route('admin.blog.index')->with('success', 'Post status updated.');
    }
}
