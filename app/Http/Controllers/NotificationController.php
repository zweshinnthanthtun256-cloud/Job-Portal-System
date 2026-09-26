<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Notifications/Index', ['notifications' => $request->user()->notifications()->paginate(20), 'preferences' => $request->user()->notificationPreference()->firstOrCreate()]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function preferences(Request $request): RedirectResponse
    {
        $data = $request->validate(['database_enabled' => ['boolean'], 'email_application_updates' => ['boolean'], 'email_interviews' => ['boolean'], 'email_recommendations' => ['boolean']]);
        $request->user()->notificationPreference()->updateOrCreate([], $data);

        return back()->with('success', 'Notification preferences saved.');
    }
}
