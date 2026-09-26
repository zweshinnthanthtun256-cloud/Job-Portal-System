<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Company;
use App\Models\Job;
use App\Models\Report;
use App\Models\Skill;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $days = collect(range(13, 0))->map(fn ($offset) => today()->subDays($offset));

        return Inertia::render('Admin/Index', [
            'users' => User::latest()->paginate(15, ['*'], 'users_page'), 'companies' => Company::latest()->paginate(15, ['*'], 'companies_page'),
            'jobs' => Job::with('company:id,name')->latest()->paginate(15, ['*'], 'jobs_page'),
            'reports' => Report::with(['reporter:id,name,email', 'reportable'])->latest()->paginate(15, ['*'], 'reports_page'),
            'audits' => ActivityLog::with('user:id,name')->latest()->limit(100)->get(), 'categories' => Category::orderBy('name')->get(), 'skills' => Skill::orderBy('name')->get(),
            'analytics' => ['totals' => ['users' => User::count(), 'companies' => Company::count(), 'jobs' => Job::count(), 'applications' => Application::count(), 'open_reports' => Report::whereIn('status', ['open', 'reviewing'])->count()], 'labels' => $days->map->format('M j'), 'jobs' => $days->map(fn ($day) => Job::whereDate('created_at', $day)->count()), 'applications' => $days->map(fn ($day) => Application::whereDate('created_at', $day)->count())],
            'settings' => AppSetting::pluck('value', 'key'),
        ]);
    }

    public function category(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', 'unique:categories,name']]);
        $category = Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);
        $logger->log('admin.category_created', $category);

        return back()->with('success', 'Category created.');
    }

    public function destroyCategory(Category $category, ActivityLogger $logger): RedirectResponse
    {
        abort_if($category->jobs()->exists(), 422, 'Category is in use.');
        $logger->log('admin.category_deleted', $category);
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function skill(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', 'unique:skills,name']]);
        $skill = Skill::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);
        $logger->log('admin.skill_created', $skill);

        return back()->with('success', 'Skill created.');
    }

    public function destroySkill(Skill $skill, ActivityLogger $logger): RedirectResponse
    {
        abort_if($skill->jobs()->exists(), 422, 'Skill is in use.');
        $logger->log('admin.skill_deleted', $skill);
        $skill->delete();

        return back()->with('success', 'Skill deleted.');
    }

    public function user(Request $request, User $user, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:active,suspended']]);
        abort_if($user->isAdmin() && $user->id !== $request->user()->id, 403);
        $old = $user->status;
        $user->update($data);
        $logger->log('account.'.$data['status'], $user, ['status' => $old], $data);

        return back()->with('success', 'User status updated.');
    }

    public function company(Request $request, Company $company, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['verification_status' => ['required', 'in:pending,verified,rejected,suspended']]);
        $old = $company->verification_status;
        $company->update($data);
        $logger->log('company.verification_changed', $company, ['verification_status' => $old], $data);

        return back()->with('success', 'Company status updated.');
    }

    public function job(Request $request, Job $job, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:published,paused,closed,archived']]);
        $old = $job->status;
        $job->update($data);
        $logger->log('admin.job_status_changed', $job, ['status' => $old], $data);

        return back()->with('success', 'Job status updated.');
    }

    public function report(Request $request, Report $report, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:reviewing,resolved,dismissed'], 'resolution' => ['nullable', 'string', 'max:3000']]);
        $old = $report->only(['status', 'resolution']);
        $report->update([...$data, 'resolved_by' => $request->user()->id]);
        $logger->log('admin.report_updated', $report, $old, $data);

        return back()->with('success', 'Report updated.');
    }

    public function settings(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['site_name' => ['required', 'string', 'max:100'], 'support_email' => ['required', 'email'], 'maintenance_message' => ['nullable', 'string', 'max:500'], 'applications_enabled' => ['required', 'boolean']]);
        foreach ($data as $key => $value) {
            AppSetting::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? (string) (int) $value : $value]);
        }
        $logger->log('admin.settings_updated', null, null, $data);

        return back()->with('success', 'Platform settings saved.');
    }
}
