<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Company;
use App\Models\Interview;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProductionFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_update_duplicate_and_archive_own_job(): void
    {
        [$employer, $company, $job] = $this->employerJob();

        $this->actingAs($employer)->patch(route('employer.jobs.update', $job), $this->jobPayload(['title' => 'Updated Engineer']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('jobs', ['id' => $job->id, 'title' => 'Updated Engineer']);

        $this->actingAs($employer)->post(route('employer.jobs.duplicate', $job))->assertRedirect();
        $this->assertDatabaseHas('jobs', ['company_id' => $company->id, 'title' => 'Updated Engineer (Copy)', 'status' => 'draft']);

        $this->actingAs($employer)->patch(route('employer.jobs.status', $job), ['status' => 'archived'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('jobs', ['id' => $job->id, 'status' => 'archived']);
    }

    public function test_candidate_can_withdraw_and_history_is_recorded(): void
    {
        Notification::fake();
        [, , $job] = $this->employerJob();
        $seeker = User::factory()->create();
        $application = Application::create(['job_id' => $job->id, 'user_id' => $seeker->id, 'status' => 'submitted', 'applied_at' => now()]);

        $this->actingAs($seeker)->patch(route('applications.withdraw', $application))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'withdrawn']);
        $this->assertDatabaseHas('application_status_histories', ['application_id' => $application->id, 'to_status' => 'withdrawn']);
    }

    public function test_overlapping_interview_is_rejected_for_candidate(): void
    {
        Notification::fake();
        [$employer, , $job] = $this->employerJob();
        $seeker = User::factory()->create();
        $application = Application::create(['job_id' => $job->id, 'user_id' => $seeker->id, 'status' => 'submitted', 'applied_at' => now()]);
        Interview::create(['application_id' => $application->id, 'created_by' => $employer->id, 'date' => now()->addDay()->toDateString(), 'start_time' => '09:00', 'end_time' => '10:00', 'timezone' => 'UTC', 'type' => 'phone']);

        $this->actingAs($employer)->post(route('interviews.store', $application), ['date' => now()->addDay()->toDateString(), 'start_time' => '09:30', 'end_time' => '10:30', 'timezone' => 'UTC', 'type' => 'phone'])->assertSessionHasErrors('start_time');
        $this->assertSame(1, Interview::count());
    }

    public function test_profile_extensions_and_notification_preferences_are_saved(): void
    {
        $seeker = User::factory()->create();
        $this->actingAs($seeker)->post(route('profile.languages.store'), ['name' => 'English', 'proficiency' => 'professional'])->assertSessionHasNoErrors();
        $this->actingAs($seeker)->post(route('profile.certifications.store'), ['name' => 'AWS Associate', 'issuer' => 'AWS'])->assertSessionHasNoErrors();
        $this->actingAs($seeker)->put(route('notifications.preferences'), ['database_enabled' => true, 'email_application_updates' => false, 'email_interviews' => true, 'email_recommendations' => false])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('languages', ['user_id' => $seeker->id, 'name' => 'English']);
        $this->assertDatabaseHas('certifications', ['user_id' => $seeker->id, 'name' => 'AWS Associate']);
        $this->assertDatabaseHas('notification_preferences', ['user_id' => $seeker->id, 'email_application_updates' => false]);
    }

    public function test_public_company_page_and_recommendations_render(): void
    {
        [, $company] = $this->employerJob();
        $this->get(route('companies.show', $company))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('jobs.recommendations'))->assertOk();
    }

    private function employerJob(): array
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $company = Company::create(['name' => 'Northstar', 'slug' => 'northstar', 'verification_status' => 'verified']);
        $company->users()->attach($employer, ['role' => 'owner']);
        $job = Job::create(['company_id' => $company->id, 'created_by' => $employer->id, 'title' => 'Engineer', 'slug' => 'engineer', 'description' => 'Build software.', 'employment_type' => 'full_time', 'work_arrangement' => 'remote', 'experience_level' => 'mid', 'salary_currency' => 'USD', 'vacancies' => 1, 'status' => 'published', 'published_at' => now()]);

        return [$employer, $company, $job];
    }

    private function jobPayload(array $overrides = []): array
    {
        return [...['title' => 'Engineer', 'description' => 'Build software.', 'employment_type' => 'full_time', 'work_arrangement' => 'remote', 'experience_level' => 'mid', 'salary_currency' => 'USD', 'salary_visible' => true, 'vacancies' => 1, 'skill_ids' => []], ...$overrides];
    }
}
