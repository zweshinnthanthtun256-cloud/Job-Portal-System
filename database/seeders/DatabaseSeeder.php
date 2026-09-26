<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Category;
use App\Models\Company;
use App\Models\Interview;
use App\Models\Job;
use App\Models\JobSeekerProfile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) && ! filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOL)) {
            $this->command?->warn('Demo accounts are only seeded in local and testing environments.');

            return;
        }

        $admin = User::updateOrCreate(['email' => 'admin@jobsphere.test'], ['name' => 'Avery Morgan', 'first_name' => 'Avery', 'last_name' => 'Morgan', 'password' => 'password', 'role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        $employer = User::updateOrCreate(['email' => 'employer@jobsphere.test'], ['name' => 'Maya Chen', 'first_name' => 'Maya', 'last_name' => 'Chen', 'password' => 'password', 'role' => 'employer', 'status' => 'active', 'email_verified_at' => now()]);
        $seeker = User::updateOrCreate(['email' => 'seeker@jobsphere.test'], ['name' => 'Noah Williams', 'first_name' => 'Noah', 'last_name' => 'Williams', 'password' => 'password', 'role' => 'job_seeker', 'status' => 'active', 'email_verified_at' => now()]);
        JobSeekerProfile::updateOrCreate(['user_id' => $seeker->id], ['headline' => 'Full-stack Laravel and React developer', 'city' => 'Yangon', 'country' => 'Myanmar', 'experience_level' => 'mid']);

        $company = Company::updateOrCreate(['slug' => 'northstar-digital'], ['name' => 'Northstar Digital', 'industry' => 'Software', 'size' => '51-200', 'email' => 'careers@northstar.test', 'city' => 'Singapore', 'country' => 'Singapore', 'description' => 'Northstar builds dependable software products for growing teams.', 'verification_status' => 'verified']);
        $company->users()->syncWithoutDetaching([$employer->id => ['role' => 'owner']]);

        $categories = collect(['Software Development', 'Design', 'Data Science', 'Marketing'])->mapWithKeys(function ($name) {
            $category = Category::firstOrCreate(['slug' => (string) str($name)->slug()], ['name' => $name]);

            return [$category->slug => $category];
        });
        $skills = collect(['Laravel', 'React', 'TypeScript', 'MySQL'])->map(fn ($name) => Skill::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]));

        $jobs = collect([
            ['title' => 'Senior Laravel Engineer', 'work_arrangement' => 'remote', 'experience_level' => 'senior', 'salary_min' => 65000, 'salary_max' => 90000],
            ['title' => 'React Product Engineer', 'work_arrangement' => 'hybrid', 'experience_level' => 'mid', 'salary_min' => 55000, 'salary_max' => 78000],
            ['title' => 'Junior Full-stack Developer', 'work_arrangement' => 'on_site', 'experience_level' => 'junior', 'salary_min' => 30000, 'salary_max' => 42000],
        ])->map(function ($data, $index) use ($company, $employer, $categories, $skills) {
            $slug = str($data['title'])->slug().'-'.($index + 1);
            $job = Job::updateOrCreate(['slug' => $slug], [...$data, 'company_id' => $company->id, 'category_id' => $categories['software-development']->id, 'created_by' => $employer->id, 'description' => 'Join a thoughtful product team building secure, useful software for customers around the world.', 'responsibilities' => "Build maintainable product features.\nReview code and improve engineering practices.\nCollaborate with design and product.", 'requirements' => "Strong web fundamentals.\nClear written communication.\nExperience shipping production software.", 'employment_type' => 'full_time', 'country' => 'Singapore', 'city' => $data['work_arrangement'] === 'remote' ? null : 'Singapore', 'salary_currency' => 'USD', 'status' => 'published', 'published_at' => now()->subDays($index + 1), 'application_deadline' => now()->addDays(30)]);
            $job->skills()->sync($skills->take($index === 1 ? 3 : 4)->pluck('id'));

            return $job;
        });

        $application = Application::updateOrCreate(['job_id' => $jobs->first()->id, 'user_id' => $seeker->id], ['cover_letter' => 'I enjoy building reliable Laravel applications and would be glad to contribute.', 'status' => 'submitted', 'applied_at' => now()->subDay()]);
        $application->history()->firstOrCreate(['to_status' => 'submitted'], ['changed_by' => $seeker->id]);
        Interview::updateOrCreate(['application_id' => $application->id], ['created_by' => $employer->id, 'date' => now()->addDays(3), 'start_time' => '10:00', 'end_time' => '11:00', 'timezone' => 'Asia/Yangon', 'type' => 'video', 'meeting_url' => 'https://meet.example.test/jobsphere-demo', 'interviewer' => 'Maya Chen']);
    }
}
