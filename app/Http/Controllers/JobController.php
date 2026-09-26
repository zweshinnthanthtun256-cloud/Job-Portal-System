<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'], 'location' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'], 'employment_type' => ['nullable', 'string', 'max:30'],
            'work_arrangement' => ['nullable', 'string', 'max:30'], 'experience_level' => ['nullable', 'string', 'max:30'],
        ]);

        $jobs = Job::published()->with(['company:id,name,slug,verification_status', 'category:id,name,slug', 'skills:id,name'])
            ->when($filters['q'] ?? null, fn (Builder $q, $term) => $q->where(fn (Builder $inner) => $inner->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")))
            ->when($filters['location'] ?? null, fn (Builder $q, $location) => $q->where(fn (Builder $inner) => $inner->where('city', 'like', "%{$location}%")->orWhere('country', 'like', "%{$location}%")))
            ->when($filters['category'] ?? null, fn (Builder $q, $slug) => $q->whereHas('category', fn (Builder $cat) => $cat->where('slug', $slug)))
            ->when($filters['employment_type'] ?? null, fn (Builder $q, $value) => $q->where('employment_type', $value))
            ->when($filters['work_arrangement'] ?? null, fn (Builder $q, $value) => $q->where('work_arrangement', $value))
            ->when($filters['experience_level'] ?? null, fn (Builder $q, $value) => $q->where('experience_level', $value))
            ->latest('published_at')->paginate(12)->withQueryString();

        return Inertia::render('Jobs/Index', ['jobs' => $jobs, 'filters' => $filters, 'categories' => Category::where('is_active', true)->orderBy('name')->get(['name', 'slug'])]);
    }

    public function show(Request $request, Job $job): Response
    {
        abort_unless($job->status === 'published' || ($request->user() && ($request->user()->isAdmin() || $request->user()->belongsToCompany($job->company_id))), 404);
        $job->load(['company', 'category', 'skills'])->loadCount('applications');

        return Inertia::render('Jobs/Show', [
            'job' => $job,
            'hasApplied' => $request->user()?->applications()->where('job_id', $job->id)->exists() ?? false,
            'isSaved' => $request->user()?->belongsToMany(Job::class, 'saved_jobs')->whereKey($job->id)->exists() ?? false,
            'similarJobs' => Job::published()->where('id', '!=', $job->id)
                ->where(fn (Builder $q) => $q->where('category_id', $job->category_id)->orWhere('experience_level', $job->experience_level)->orWhere('city', $job->city))
                ->with('company:id,name,slug')->latest('published_at')->limit(4)->get(),
        ]);
    }

    public function recommendations(Request $request): Response
    {
        $user = $request->user()->load(['profile', 'skills']);
        $skillIds = $user->skills->pluck('id');
        $jobs = Job::published()->with(['company:id,name,slug,verification_status', 'category:id,name', 'skills:id,name'])
            ->withCount(['skills as matching_skills_count' => fn ($q) => $q->whereIn('skills.id', $skillIds)])
            ->when($user->profile?->preferred_location, fn ($q, $location) => $q->orderByRaw('CASE WHEN city = ? OR country = ? THEN 0 ELSE 1 END', [$location, $location]))
            ->orderByDesc('matching_skills_count')->latest('published_at')->limit(20)->get();

        return Inertia::render('Jobs/Recommendations', ['jobs' => $jobs]);
    }
}
