<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Company;
use App\Models\Interview;
use App\Models\Job;
use App\Models\Report;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleWorkflowSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_primary_page_renders_for_its_intended_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@jobsphere.test')->firstOrFail();
        $employer = User::where('email', 'employer@jobsphere.test')->firstOrFail();
        $seeker = User::where('email', 'seeker@jobsphere.test')->firstOrFail();
        $company = Company::where('slug', 'northstar-digital')->firstOrFail();
        $job = Job::where('status', 'published')->firstOrFail();

        foreach ([
            route('home'), route('jobs.index'), route('jobs.show', $job),
            route('companies.show', $company), route('login'), route('register'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        foreach ([
            route('dashboard'), route('profile.edit'), route('jobs.recommendations'),
            route('applications.index'), route('saved-jobs.index'), route('interviews.index'),
            route('notifications.index'), route('jobs.index'), route('jobs.show', $job),
        ] as $url) {
            $this->actingAs($seeker)->get($url)->assertOk();
        }

        $this->actingAs($employer)->get(route('companies.mine'))->assertRedirect(route('companies.edit', $company));
        foreach ([
            route('dashboard'), route('companies.edit', $company), route('employer.jobs.index'),
            route('employer.jobs.edit', $job), route('employer.candidates.index'),
            route('interviews.index'), route('notifications.index'), route('jobs.index'),
        ] as $url) {
            $this->actingAs($employer)->get($url)->assertOk();
        }

        foreach ([route('dashboard'), route('admin.index'), route('notifications.index'), route('jobs.index')] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_company_candidate_interview_and_notification_workflow_has_no_server_errors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $employer = User::where('email', 'employer@jobsphere.test')->firstOrFail();
        $seeker = User::where('email', 'seeker@jobsphere.test')->firstOrFail();
        $company = Company::where('slug', 'northstar-digital')->firstOrFail();
        $application = Application::where('user_id', $seeker->id)->firstOrFail();
        $interview = Interview::where('application_id', $application->id)->firstOrFail();
        $member = User::factory()->create(['role' => 'employer']);

        $this->actingAs($employer)->patch(route('companies.update', $company), [
            'name' => 'Northstar Digital',
            'industry' => 'Software and Technology',
            'country' => 'Singapore',
            'city' => 'Singapore',
            'description' => 'A product engineering company.',
            'culture' => 'Collaborative and inclusive.',
            'benefits' => ['Remote work', 'Learning budget'],
        ])->assertSessionHasNoErrors();

        $this->actingAs($employer)->post(route('companies.members.store', $company), [
            'email' => $member->email,
            'role' => 'recruiter',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('company_user', ['company_id' => $company->id, 'user_id' => $member->id, 'role' => 'recruiter']);

        $this->actingAs($employer)->patch(route('employer.candidates.update', $application), [
            'status' => 'under_review',
        ])->assertSessionHasNoErrors();

        $this->actingAs($employer)->patch(route('interviews.update', $interview), [
            'date' => now()->addDays(5)->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'timezone' => 'Asia/Yangon',
            'type' => 'video',
            'meeting_url' => 'https://meet.example.test/updated-interview',
            'interviewer' => 'Maya Chen',
        ])->assertSessionHasNoErrors();

        $this->actingAs($employer)->patch(route('interviews.cancel', $interview), [
            'reason' => 'The hiring team needs to reschedule.',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('interviews', ['id' => $interview->id, 'status' => 'cancelled']);

        $notification = $seeker->notifications()->firstOrFail();
        $this->actingAs($seeker)->patch(route('notifications.read', $notification->id))->assertRedirect();
        $this->actingAs($seeker)->post(route('notifications.read-all'))->assertSessionHasNoErrors();
        $this->assertSame(0, $seeker->fresh()->unreadNotifications()->count());

        $this->actingAs($employer)->delete(route('companies.members.destroy', [$company, $member]))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('company_user', ['company_id' => $company->id, 'user_id' => $member->id]);
    }

    public function test_reporting_and_admin_management_workflow_has_no_server_errors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@jobsphere.test')->firstOrFail();
        $seeker = User::where('email', 'seeker@jobsphere.test')->firstOrFail();
        $company = Company::where('slug', 'northstar-digital')->firstOrFail();
        $job = Job::where('status', 'published')->firstOrFail();

        $this->actingAs($seeker)->post(route('reports.store', $job), [
            'reason' => 'misleading',
            'details' => 'The salary details should be reviewed.',
        ])->assertSessionHasNoErrors();
        $report = Report::firstOrFail();

        $this->actingAs($admin)->patch(route('admin.reports.update', $report), [
            'status' => 'resolved',
            'resolution' => 'The employer confirmed and corrected the listing.',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.companies.update', $company), ['verification_status' => 'verified'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.jobs.update', $job), ['status' => 'paused'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->put(route('admin.settings'), [
            'site_name' => 'JobSphere',
            'support_email' => 'support@jobsphere.test',
            'maintenance_message' => '',
            'applications_enabled' => true,
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Quality Assurance'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.skills.store'), ['name' => 'Playwright'])->assertSessionHasNoErrors();
        $category = Category::where('name', 'Quality Assurance')->firstOrFail();
        $skill = Skill::where('name', 'Playwright')->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertSessionHasNoErrors();
        $this->actingAs($admin)->delete(route('admin.skills.destroy', $skill))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'resolved']);
        $this->assertDatabaseHas('app_settings', ['key' => 'site_name', 'value' => 'JobSphere']);
    }
}
