<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'category_id', 'created_by', 'title', 'slug', 'description',
        'responsibilities', 'requirements', 'employment_type', 'work_arrangement',
        'experience_level', 'country', 'city', 'salary_min', 'salary_max',
        'salary_currency', 'salary_visible', 'vacancies', 'status', 'published_at',
        'application_deadline',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'application_deadline' => 'date',
            'salary_visible' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->where(fn (Builder $q) => $q->whereNull('application_deadline')->orWhereDate('application_deadline', '>=', today()));
    }

    public function isAcceptingApplications(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast()
            && (! $this->application_deadline || $this->application_deadline->isToday() || $this->application_deadline->isFuture());
    }
}
