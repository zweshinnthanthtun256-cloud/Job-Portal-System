<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\AppSetting;
use App\Models\Job;
use App\Notifications\ApplicationUpdateNotification;
use App\Services\ActivityLogger;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Applications/Index', [
            'applications' => $request->user()->applications()->with(['job.company:id,name', 'history' => fn ($q) => $q->oldest()])->latest('applied_at')->paginate(15),
        ]);
    }

    public function store(StoreApplicationRequest $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        if (AppSetting::valueFor('applications_enabled', '1') === '0') {
            throw ValidationException::withMessages(['job' => 'Applications are temporarily disabled.']);
        }
        if (! $job->isAcceptingApplications()) {
            throw ValidationException::withMessages(['job' => 'This job is no longer accepting applications.']);
        }

        try {
            $application = DB::transaction(function () use ($request, $job) {
                $application = Application::create([...$request->validated(), 'job_id' => $job->id, 'user_id' => $request->user()->id, 'status' => 'submitted', 'applied_at' => now()]);
                $application->history()->create(['changed_by' => $request->user()->id, 'to_status' => 'submitted']);

                return $application;
            });
            $logger->log('application.submitted', $application);
            Notification::send($job->company->users, new ApplicationUpdateNotification($application, 'New application', $request->user()->name.' applied for '.$job->title.'.'));
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['job' => 'You have already applied for this job.']);
        }

        return back()->with('success', 'Application submitted successfully.');
    }

    public function withdraw(Request $request, Application $application, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($application->user_id === $request->user()->id, 403);
        abort_if(in_array($application->status, ['withdrawn', 'hired', 'rejected'], true), 422, 'This application can no longer be withdrawn.');
        $from = $application->status;
        DB::transaction(function () use ($application, $request, $from) {
            $application->update(['status' => 'withdrawn']);
            $application->history()->create(['changed_by' => $request->user()->id, 'from_status' => $from, 'to_status' => 'withdrawn', 'note' => 'Withdrawn by candidate']);
        });
        $logger->log('application.withdrawn', $application, ['status' => $from], ['status' => 'withdrawn']);
        Notification::send($application->job->company->users, new ApplicationUpdateNotification($application, 'Application withdrawn', $request->user()->name.' withdrew their application for '.$application->job->title.'.'));

        return back()->with('success', 'Application withdrawn.');
    }
}
