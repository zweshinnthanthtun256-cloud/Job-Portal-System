<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function create(User $user, int $companyId): bool
    {
        return $user->role === 'employer' && $user->belongsToCompany($companyId);
    }

    public function update(User $user, Job $job): bool
    {
        return $user->isAdmin() || ($user->role === 'employer' && $user->belongsToCompany($job->company_id));
    }
}
