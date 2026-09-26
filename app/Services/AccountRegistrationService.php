<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountRegistrationService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['account_type'],
            ]);

            if ($data['account_type'] === 'employer') {
                $company = Company::create([
                    'name' => $data['company_name'],
                    'slug' => $this->uniqueSlug($data['company_name']),
                    'industry' => Arr::get($data, 'industry'),
                    'email' => $data['email'],
                ]);
                $company->users()->attach($user, ['role' => 'owner']);
            } else {
                JobSeekerProfile::create(['user_id' => $user->id]);
            }

            return $user;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $counter = 2;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
