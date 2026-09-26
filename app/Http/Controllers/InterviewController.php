<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterviewRequest;
use App\Models\Application;
use App\Models\Interview;
use App\Notifications\InterviewScheduledNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    public function index(Request $request): Response
    {
        if ($request->user()->role === 'employer') {
            $ids = $request->user()->companies()->pluck('companies.id');
            $query = Interview::whereHas('application.job', fn ($q) => $q->whereIn('company_id', $ids));
        } else {
            $query = Interview::whereHas('application', fn ($q) => $q->where('user_id', $request->user()->id));
        }

        return Inertia::render('Interviews/Index', ['interviews' => $query->with(['application.user:id,name', 'application.job:id,title'])->orderBy('date')->orderBy('start_time')->paginate(20)]);
    }

    public function store(StoreInterviewRequest $request, Application $application, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($request->user()->belongsToCompany($application->job()->value('company_id')), 403);
        $this->ensureNoConflict($request, $application);
        $interview = DB::transaction(function () use ($request, $application) {
            $interview = $application->interviews()->create([...$request->validated(), 'created_by' => $request->user()->id]);
            $from = $application->status;
            $application->update(['status' => 'interview_scheduled']);
            $application->history()->create(['changed_by' => $request->user()->id, 'from_status' => $from, 'to_status' => 'interview_scheduled']);

            return $interview;
        });
        $application->user->notify(new InterviewScheduledNotification($interview));
        $logger->log('interview.scheduled', $interview);

        return back()->with('success', 'Interview scheduled and candidate notified.');
    }

    public function update(StoreInterviewRequest $request, Interview $interview, ActivityLogger $logger): RedirectResponse
    {
        $interview->load('application.job');
        abort_unless($request->user()->belongsToCompany($interview->application->job->company_id), 403);
        $this->ensureNoConflict($request, $interview->application, $interview->id);
        $old = $interview->only(array_keys($request->validated()));
        $interview->update([...$request->validated(), 'status' => 'scheduled', 'cancelled_at' => null, 'cancellation_reason' => null]);
        $logger->log('interview.updated', $interview, $old, $request->validated());
        $interview->application->user->notify(new InterviewScheduledNotification($interview, 'Interview updated'));

        return back()->with('success', 'Interview updated and candidate notified.');
    }

    public function cancel(Request $request, Interview $interview, ActivityLogger $logger): RedirectResponse
    {
        $interview->load('application.job');
        abort_unless($request->user()->belongsToCompany($interview->application->job->company_id), 403);
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        abort_if($interview->status === 'cancelled', 422, 'Interview is already cancelled.');
        $interview->update(['status' => 'cancelled', 'cancellation_reason' => $data['reason'], 'cancelled_at' => now()]);
        $logger->log('interview.cancelled', $interview, ['status' => 'scheduled'], ['status' => 'cancelled', 'reason' => $data['reason']]);
        $interview->application->user->notify(new InterviewScheduledNotification($interview, 'Interview cancelled'));

        return back()->with('success', 'Interview cancelled and candidate notified.');
    }

    private function ensureNoConflict(StoreInterviewRequest $request, Application $application, ?int $ignore = null): void
    {
        $conflict = Interview::where('date', $request->date)->where('status', 'scheduled')
            ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))
            ->where(fn ($q) => $q->where('created_by', $request->user()->id)->orWhereHas('application', fn ($a) => $a->where('user_id', $application->user_id)))
            ->where('start_time', '<', $request->end_time)->where('end_time', '>', $request->start_time)->exists();
        if ($conflict) {
            throw ValidationException::withMessages(['start_time' => 'This time overlaps another interview for the interviewer or candidate.']);
        }
    }
}
