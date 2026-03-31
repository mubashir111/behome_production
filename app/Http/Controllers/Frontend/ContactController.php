<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Your message has been sent. We\'ll be in touch soon!',
        ]);
    }
}
