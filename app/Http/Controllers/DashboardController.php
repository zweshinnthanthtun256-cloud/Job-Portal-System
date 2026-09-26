<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            $stats = ['Users' => User::count(), 'Companies' => Company::count(), 'Active jobs' => Job::published()->count(), 'Applications' => Application::count()];
            $recent = User::latest()->limit(6)->get(['id', 'name', 'email', 'role', 'created_at']);
        } elseif ($user->role === 'employer') {
            $companyIds = $user->companies()->pluck('companies.id');
            $stats = [
                'Active jobs' => Job::whereIn('company_id', $companyIds)->published()->count(),
                'Applications' => Application::whereHas('job', fn ($q) => $q->whereIn('company_id', $companyIds))->count(),
                'Shortlisted' => Application::whereHas('job', fn ($q) => $q->whereIn('company_id', $companyIds))->where('status', 'shortlisted')->count(),
                'Hires' => Application::whereHas('job', fn ($q) => $q->whereIn('company_id', $companyIds))->where('status', 'hired')->count(),
            ];
            $recent = Application::whereHas('job', fn ($q) => $q->whereIn('company_id', $companyIds))->with(['user:id,name', 'job:id,title'])->latest('applied_at')->limit(6)->get();
        } else {
            $stats = [
                'Applications' => $user->applications()->count(),
                'Under review' => $user->applications()->whereIn('status', ['viewed', 'under_review', 'shortlisted'])->count(),
                'Interviews' => $user->applications()->where('status', 'interview_scheduled')->count(),
                'Saved jobs' => $user->belongsToMany(Job::class, 'saved_jobs')->count(),
            ];
            $recent = $user->applications()->with('job.company:id,name')->latest('applied_at')->limit(6)->get();
        }

        return Inertia::render('Dashboard', ['stats' => $stats, 'recent' => $recent]);
    }
}
