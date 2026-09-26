<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtendedSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_resume_is_hidden_from_unrelated_employer(): void
    {
        Storage::fake('local');
        $seeker = User::factory()->create();
        $employer = User::factory()->create(['role' => 'employer']);
        Storage::disk('local')->put('resumes/one.pdf', 'pdf');
        $resume = Resume::create(['user_id' => $seeker->id, 'original_name' => 'resume.pdf', 'path' => 'resumes/one.pdf', 'mime_type' => 'application/pdf', 'size' => 3]);

        $this->actingAs($employer)->get(route('resumes.download', $resume))->assertForbidden();
    }

    public function test_company_recruiter_cannot_add_company_admin(): void
    {
        $recruiter = User::factory()->create(['role' => 'employer']);
        $candidate = User::factory()->create(['role' => 'employer']);
        $company = Company::create(['name' => 'Northstar', 'slug' => 'northstar']);
        $company->users()->attach($recruiter, ['role' => 'recruiter']);

        $this->actingAs($recruiter)->post(route('companies.members.store', $company), ['email' => $candidate->email, 'role' => 'company_admin'])->assertForbidden();
    }

    public function test_employer_cannot_schedule_interview_for_another_company(): void
    {
        $owner = User::factory()->create(['role' => 'employer']);
        $outsider = User::factory()->create(['role' => 'employer']);
        $seeker = User::factory()->create();
        $company = Company::create(['name' => 'Northstar', 'slug' => 'northstar']);
        $company->users()->attach($owner, ['role' => 'owner']);
        $job = Job::create(['company_id' => $company->id, 'created_by' => $owner->id, 'title' => 'Engineer', 'slug' => 'engineer', 'description' => 'Build software.', 'employment_type' => 'full_time', 'work_arrangement' => 'remote', 'experience_level' => 'mid', 'status' => 'published', 'published_at' => now()]);
        $application = Application::create(['job_id' => $job->id, 'user_id' => $seeker->id, 'status' => 'submitted', 'applied_at' => now()]);

        $this->actingAs($outsider)->post(route('interviews.store', $application), ['date' => now()->addDay()->toDateString(), 'start_time' => '09:00', 'end_time' => '10:00', 'timezone' => 'UTC', 'type' => 'phone'])->assertForbidden();
    }

    public function test_non_admin_cannot_open_admin_console(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.index'))->assertForbidden();
    }
}
