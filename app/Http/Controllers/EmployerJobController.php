<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Category;
use App\Models\Job;
use App\Models\Skill;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EmployerJobController extends Controller
{
    public function index(Request $request): Response
    {
        $companies = $request->user()->companies()->get(['companies.id', 'companies.name']);

        return Inertia::render('Employer/Jobs/Index', [
            'companies' => $companies,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'jobs' => Job::whereIn('company_id', $companies->pluck('id'))->with('company:id,name')->withCount('applications')->latest()->paginate(15),
        ]);
    }

    public function store(StoreJobRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->safe()->except(['publish', 'skill_ids']);
        abort_unless($request->user()->belongsToCompany((int) $data['company_id']), 403);
        $data['created_by'] = $request->user()->id;
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(6));
        $data['status'] = $request->boolean('publish') ? 'published' : 'draft';
        $data['published_at'] = $request->boolean('publish') ? now() : null;
        $job = Job::create($data);
        $job->skills()->sync($request->input('skill_ids', []));
        $logger->log('job.created', $job, null, $job->only(['title', 'status', 'company_id']));

        return redirect()->route('jobs.show', $job)->with('success', 'Job created successfully.');
    }

    public function edit(Request $request, Job $job): Response
    {
        $this->authorizeJob($request, $job);

        return Inertia::render('Employer/Jobs/Edit', [
            'job' => $job->load('skills:id,name'),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'skillLibrary' => Skill::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateJobRequest $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeJob($request, $job);
        $data = $request->safe()->except('skill_ids');
        $old = $job->only(array_keys($data));
        $job->update($data);
        $job->skills()->sync($request->input('skill_ids', []));
        $logger->log('job.updated', $job, $old, $data);

        return back()->with('success', 'Job updated.');
    }

    public function duplicate(Request $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeJob($request, $job);
        $copy = $job->replicate(['slug', 'status', 'published_at']);
        $copy->title = $job->title.' (Copy)';
        $copy->slug = Str::slug($copy->title).'-'.Str::lower(Str::random(6));
        $copy->status = 'draft';
        $copy->published_at = null;
        $copy->created_by = $request->user()->id;
        $copy->save();
        $copy->skills()->sync($job->skills()->pluck('skills.id'));
        $logger->log('job.duplicated', $copy, null, ['source_job_id' => $job->id]);

        return redirect()->route('employer.jobs.edit', $copy)->with('success', 'Draft copy created.');
    }

    public function status(Request $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeJob($request, $job);
        $data = $request->validate(['status' => ['required', 'in:draft,published,paused,closed,archived']]);
        $old = $job->status;
        $job->update(['status' => $data['status'], 'published_at' => $data['status'] === 'published' ? ($job->published_at ?? now()) : $job->published_at]);
        $logger->log('job.status_changed', $job, ['status' => $old], ['status' => $data['status']]);

        return back()->with('success', 'Job status changed to '.str_replace('_', ' ', $data['status']).'.');
    }

    public function destroy(Request $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeJob($request, $job);
        abort_if($job->applications()->exists(), 422, 'Jobs with applications must be archived instead of deleted.');
        $logger->log('job.deleted', $job);
        $job->delete();

        return redirect()->route('employer.jobs.index')->with('success', 'Job deleted.');
    }

    private function authorizeJob(Request $request, Job $job): void
    {
        abort_unless($request->user()->belongsToCompany($job->company_id), 403);
    }
}
