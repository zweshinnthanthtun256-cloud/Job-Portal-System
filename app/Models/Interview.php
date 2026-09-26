<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['date' => 'date', 'cancelled_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
