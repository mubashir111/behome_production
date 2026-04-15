<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->email));

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $email],
            ['active' => true]
        );

        if (!$subscriber->wasRecentlyCreated && !$subscriber->active) {
            $subscriber->update(['active' => true]);
        }

        return response()->json([
            'status'  => true,
            'message' => "You're subscribed! Thank you for joining us.",
        ]);
    }
}
