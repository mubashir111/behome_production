<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;

class BlogCommentController extends Controller
{
    public function index()
    {
        $comments = BlogComment::with('post')->latest()->paginate(20);
        return view('admin.blog-comments.index', compact('comments'));
    }

    public function approve(BlogComment $comment)
    {
        $comment->update(['is_approved' => !$comment->is_approved]);
        $label = $comment->is_approved ? 'approved' : 'unapproved';
        return back()->with('success', "Comment {$label}.");
    }

    public function destroy(BlogComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted.');
    }
}
