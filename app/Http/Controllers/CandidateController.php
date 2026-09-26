<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Notifications\ApplicationUpdateNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    public function index(Request $request): Response
    {
        $companyIds = $request->user()->companies()->pluck('companies.id');
        $applications = Application::whereHas('job', fn ($query) => $query->whereIn('company_id', $companyIds))
            ->with(['user:id,name,email', 'job:id,title,company_id'])->latest('applied_at')->paginate(20);

        return Inertia::render('Employer/Candidates/Index', ['applications' => $applications]);
    }

    public function update(Request $request, Application $application, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($request->user()->belongsToCompany($application->job()->value('company_id')), 403);
        $data = $request->validate(['status' => ['required', Rule::in(['viewed', 'under_review', 'shortlisted', 'interview_scheduled', 'offered', 'hired', 'rejected'])]]);

        $from = $application->status;
        DB::transaction(function () use ($application, $request, $data, $from) {
            $application->update(['status' => $data['status']]);
            $application->history()->create(['changed_by' => $request->user()->id, 'from_status' => $from, 'to_status' => $data['status']]);
        });
        $logger->log('application.status_changed', $application, ['status' => $from], ['status' => $data['status']]);
        $application->user->notify(new ApplicationUpdateNotification($application, 'Application updated', 'Your application for '.$application->job->title.' is now '.str_replace('_', ' ', $data['status']).'.'));

        return back()->with('success', 'Candidate status updated.');
    }
}
