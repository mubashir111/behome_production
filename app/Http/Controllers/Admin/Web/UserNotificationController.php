<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index()
    {
        $notifications = UserNotification::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.user_notifications.index', compact('notifications'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.user_notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => ['required', 'string', 'max:200'],
            'body'    => ['nullable', 'string', 'max:500'],
            'type'    => ['required', 'in:info,success,warning,promo'],
            'icon'    => ['required', 'in:bell,gift,check,warning,truck,return,x'],
            'color'   => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'link'    => ['nullable', 'string', 'max:500'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $notif = UserNotification::send(
            title:  $data['title'],
            body:   $data['body'] ?? '',
            type:   $data['type'],
            icon:   $data['icon'],
            color:  $data['color'],
            link:   $data['link'] ?? '',
            userId: $data['user_id'] ?? null,
        );

        // Record in admin bell so the sender gets instant feedback
        $recipient = $notif->user_id
            ? (User::find($notif->user_id)?->name ?? 'a user')
            : 'all users';
        AdminNotification::record(
            type:  'notification_sent',
            title: 'Notification sent to ' . $recipient,
            body:  $data['title'],
            link:  route('admin.user-notifications.index'),
            icon:  'bell',
        );

        return redirect()->route('admin.user-notifications.index')
            ->with('success', 'Notification sent successfully.');
    }

    public function destroy(UserNotification $userNotification)
    {
        $userNotification->delete();
        return back()->with('success', 'Notification deleted.');
    }
}
