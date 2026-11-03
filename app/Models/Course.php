<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'lecturer',
        'schedule',
        'capacity',
        'enrolled',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'enrolled' => 'integer',
        ];
    }
}
