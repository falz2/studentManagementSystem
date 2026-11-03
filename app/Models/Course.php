<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['name', 'code', 'lecturer_id', 'schedule', 'capacity', 'status'];

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(LecturerProfile::class, 'lecturer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
