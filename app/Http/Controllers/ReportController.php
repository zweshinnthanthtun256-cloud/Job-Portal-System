<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Report;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'in:spam,misleading,inappropriate,fraud,other'], 'details' => ['nullable', 'string', 'max:3000']]);
        $report = Report::create([...$data, 'reporter_id' => $request->user()->id, 'reportable_type' => $job->getMorphClass(), 'reportable_id' => $job->id]);
        $logger->log('report.submitted', $report);

        return back()->with('success', 'Report submitted for review.');
    }
}
