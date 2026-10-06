<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_can_register_and_receives_profile(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'first_name' => 'Nora', 'last_name' => 'Stone', 'email' => 'nora@example.test',
            'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123', 'account_type' => 'job_seeker',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $user = User::where('email', 'nora@example.test')->firstOrFail();
        $this->assertDatabaseHas('job_seeker_profiles', ['user_id' => $user->id]);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_employer_registration_atomically_creates_owned_company(): void
    {
        Notification::fake();

        $this->post('/register', [
            'first_name' => 'Maya', 'last_name' => 'Chen', 'email' => 'maya@example.test',
            'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123', 'account_type' => 'employer',
            'company_name' => 'Bright River Labs', 'industry' => 'Software',
        ])->assertRedirect(route('verification.notice'));

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

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('verification.notice'))->assertOk();
    }

    public function test_user_can_verify_email_with_a_signed_link(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_request_another_verification_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.send'))
            ->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }
}
