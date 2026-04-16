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
        $email = $subscriber->email;
        $subscriber->delete();
        
        \App\Models\AdminNotification::record('warning', 'Subscriber Removed', "Email '{$email}' was removed from the subscriber list by " . (auth()->user()->name ?? 'Admin'));
        
        return back()->with('success', 'Subscriber removed.');
    }
}
