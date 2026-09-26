<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['path'];

    protected $casts = ['is_primary' => 'boolean'];
}
