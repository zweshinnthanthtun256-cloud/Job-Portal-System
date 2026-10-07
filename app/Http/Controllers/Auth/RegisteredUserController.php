<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AccountRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request, AccountRegistrationService $service): RedirectResponse
    {
        $user = $service->register($request->validated());
        Auth::login($user);
        event(new Registered($user));
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welcome to JobSphere.');
    }
}
