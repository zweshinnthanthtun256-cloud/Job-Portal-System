<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavedJobController extends Controller
{
    public function index(Request $request): Response
    {
        $jobs = $request->user()->belongsToMany(Job::class, 'saved_jobs')
            ->with(['company:id,name,slug,verification_status'])->latest('saved_jobs.created_at')->paginate(12);

        return Inertia::render('SavedJobs/Index', ['jobs' => $jobs]);
    }

    public function store(Request $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($request->user()->role === 'job_seeker' && $job->status === 'published', 403);
        $request->user()->belongsToMany(Job::class, 'saved_jobs')->syncWithoutDetaching($job->id);
        $logger->log('job.saved', $job);

        return back()->with('success', 'Job saved.');
    }

    public function destroy(Request $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        $request->user()->belongsToMany(Job::class, 'saved_jobs')->detach($job->id);
        $logger->log('job.unsaved', $job);

        return back()->with('success', 'Job removed from saved jobs.');
    }
}
