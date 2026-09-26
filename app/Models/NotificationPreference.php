<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['database_enabled' => 'boolean', 'email_application_updates' => 'boolean', 'email_interviews' => 'boolean', 'email_recommendations' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
