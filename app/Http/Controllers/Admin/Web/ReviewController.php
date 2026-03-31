<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::with(['product', 'user'])->latest();

        if ($request->filled('search')) {
            $query->where('review', 'like', '%' . $request->search . '%')
                ->orWhereHas('product', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
                ->orWhereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('star')) {
            $query->where('star', $request->star);
        }

        $reviews = $query->paginate(20)->withQueryString();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(ProductReview $review)
    {
        $review->load(['product', 'user']);
        return view('admin.reviews.show', compact('review'));
    }

    public function destroy(ProductReview $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }
}
