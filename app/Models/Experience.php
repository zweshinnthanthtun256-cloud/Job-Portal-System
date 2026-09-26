<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'currently_working' => 'boolean'];
}
