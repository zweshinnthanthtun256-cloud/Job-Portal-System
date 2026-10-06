<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_can_register_and_receives_profile(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Nora', 'last_name' => 'Stone', 'email' => 'nora@example.test',
            'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123', 'account_type' => 'job_seeker',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $user = User::where('email', 'nora@example.test')->firstOrFail();
        $this->assertDatabaseHas('job_seeker_profiles', ['user_id' => $user->id]);
    }

    public function test_employer_registration_atomically_creates_owned_company(): void
    {
        $this->post('/register', [
            'first_name' => 'Maya', 'last_name' => 'Chen', 'email' => 'maya@example.test',
            'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123', 'account_type' => 'employer',
            'company_name' => 'Bright River Labs', 'industry' => 'Software',
        ])->assertRedirect(route('dashboard'));

        $company = Company::where('slug', 'bright-river-labs')->firstOrFail();
        $this->assertDatabaseHas('company_user', ['company_id' => $company->id, 'role' => 'owner']);
    }

    public function test_login_regenerates_session_and_logout_invalidates_it(): void
    {
        $user = User::factory()->create(['password' => 'SecurePass123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'SecurePass123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
