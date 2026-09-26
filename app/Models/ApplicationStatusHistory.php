<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    protected $fillable = ['application_id', 'changed_by', 'from_status', 'to_status', 'note'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
