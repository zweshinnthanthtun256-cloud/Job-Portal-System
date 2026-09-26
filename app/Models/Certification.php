<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    protected $fillable = ['user_id', 'name', 'issuer', 'issued_at', 'expires_at', 'credential_url'];

    protected $casts = ['issued_at' => 'date', 'expires_at' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
