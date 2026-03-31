<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;

class SubscriberController extends Controller
{
    public function index()
    {
        $subscribers = Subscriber::latest()->paginate(20);
        return view('admin.subscribers.index', compact('subscribers'));
    }

    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();
        return back()->with('success', 'Subscriber removed.');
    }
}
