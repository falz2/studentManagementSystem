<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'lecturer_id',
        'schedule',
        'capacity',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    /**
     * The lecturer (profile) that teaches this course.
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(LecturerProfile::class, 'lecturer_id');
    }

    /**
     * Registrations for this course.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Students registered for this course (through registrations).
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            StudentProfile::class,
            'registrations',
            'course_id',
            'student_id'
        )->withPivot('status', 'registered_at')->withTimestamps();
    }

    /**
     * Convenience: number of active/pending registrations.
     */
    public function enrolledCount(): int
    {
        return $this->registrations()
            ->whereIn('status', ['pending', 'approved', 'enrolled'])
            ->count();
    }

    /**
     * Convenience: is this course full?
     */
    public function isFull(): bool
    {
        return $this->enrolledCount() >= $this->capacity;
    }

    /**
     * Convenience: is this course open for registration?
     */
    public function isOpen(): bool
    {
        return $this->status === 'open' && ! $this->isFull();
    }
}