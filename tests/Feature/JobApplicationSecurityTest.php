<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_can_apply_once_to_active_job(): void
    {
        [$job, $seeker] = $this->jobAndSeeker();
        $this->actingAs($seeker)->post(route('applications.store', $job), ['cover_letter' => 'A useful, concise letter.'])->assertRedirect();
        $this->assertDatabaseHas('applications', ['job_id' => $job->id, 'user_id' => $seeker->id, 'status' => 'submitted']);
        $this->assertDatabaseHas('application_status_histories', ['to_status' => 'submitted']);
        $this->actingAs($seeker)->post(route('applications.store', $job))->assertSessionHasErrors('job');
        $this->assertDatabaseCount('applications', 1);
    }

    public function test_expired_job_rejects_application(): void
    {
        [$job, $seeker] = $this->jobAndSeeker();
        $job->update(['application_deadline' => now()->subDay()]);
        $this->actingAs($seeker)->post(route('applications.store', $job))->assertSessionHasErrors('job');
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_employer_cannot_create_job_for_another_company(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $own = Company::create(['name' => 'Own Co', 'slug' => 'own-co']);
        $other = Company::create(['name' => 'Other Co', 'slug' => 'other-co']);
        $own->users()->attach($employer, ['role' => 'owner']);
        $this->actingAs($employer)->post(route('employer.jobs.store'), $this->validJobData($other->id))->assertForbidden();
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_job_seeker_cannot_access_employer_job_creation(): void
    {
        $seeker = User::factory()->create();
        $company = Company::create(['name' => 'Other Co', 'slug' => 'other-co']);
        $this->actingAs($seeker)->post(route('employer.jobs.store'), $this->validJobData($company->id))->assertForbidden();
    }

    private function jobAndSeeker(): array
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $seeker = User::factory()->create();
        $company = Company::create(['name' => 'Northstar', 'slug' => 'northstar']);
        $company->users()->attach($employer, ['role' => 'owner']);
        $job = Job::create([...$this->validJobData($company->id), 'created_by' => $employer->id, 'slug' => 'laravel-engineer', 'status' => 'published', 'published_at' => now()->subHour()]);

        return [$job, $seeker];
    }

    private function validJobData(int $companyId): array
    {
        return ['company_id' => $companyId, 'title' => 'Laravel Engineer', 'description' => 'Build secure products.', 'employment_type' => 'full_time', 'work_arrangement' => 'remote', 'experience_level' => 'mid', 'salary_currency' => 'USD', 'vacancies' => 1, 'application_deadline' => now()->addMonth()->toDateString()];
    }
}
