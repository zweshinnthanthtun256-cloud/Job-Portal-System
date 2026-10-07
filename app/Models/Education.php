<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'educations';

    protected $guarded = ['id'];

    protected $casts = ['currently_studying' => 'boolean'];
}
