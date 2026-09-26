<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Home', [
            'featuredJobs' => Job::published()->with('company:id,name,slug,verification_status')
                ->latest('published_at')->limit(6)->get(),
            'stats' => [
                'jobs' => Job::published()->count(),
                'companies' => Company::count(),
                'talent' => User::where('role', 'job_seeker')->count(),
            ],
        ]);
    }
}
