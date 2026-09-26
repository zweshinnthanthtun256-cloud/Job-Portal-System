<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => ['user' => $request->user()?->only('id', 'name', 'first_name', 'last_name', 'email', 'role')],
            'unreadNotifications' => $request->user()?->unreadNotifications()->count() ?? 0,
            'flash' => ['success' => fn () => $request->session()->get('success')],
        ];
    }
}
