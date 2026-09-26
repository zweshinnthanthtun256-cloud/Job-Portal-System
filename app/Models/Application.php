<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = ['job_id', 'user_id', 'cover_letter', 'expected_salary', 'availability_date', 'status', 'applied_at'];

    protected $hidden = ['employer_notes'];

    protected $casts = ['applied_at' => 'datetime', 'availability_date' => 'date'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
