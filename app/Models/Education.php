<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['currently_studying' => 'boolean'];
}
