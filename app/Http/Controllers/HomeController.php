<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
            'featuredCompanies' => Company::query()->select(['id', 'name', 'slug', 'logo_path', 'industry', 'city'])
                ->whereHas('jobs', fn ($query) => $query->published())
                ->withCount(['jobs' => fn ($query) => $query->published()])
                ->orderByDesc('jobs_count')->limit(7)->get(),
            'categories' => Category::query()->select(['id', 'name', 'slug'])->where('is_active', true)
                ->withCount(['jobs' => fn ($query) => $query->published()])
                ->orderByDesc('jobs_count')->limit(8)->get(),
            'stats' => [
                'jobs' => Job::published()->count(),
                'companies' => Company::count(),
                'talent' => User::where('role', 'job_seeker')->count(),
            ],
        ]);
    }
}
