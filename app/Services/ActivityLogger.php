<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(string $event, ?Model $subject = null, ?array $old = null, ?array $new = null): void
    {
        ActivityLog::create(['user_id' => auth()->id(), 'event' => $event, 'subject_type' => $subject?->getMorphClass(), 'subject_id' => $subject?->getKey(), 'old_values' => $old, 'new_values' => $new, 'ip_address' => request()->ip(), 'user_agent' => substr((string) request()->userAgent(), 0, 1000)]);
    }
}
