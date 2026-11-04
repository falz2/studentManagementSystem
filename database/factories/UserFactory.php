<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'student',
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(?callable $callback = null): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn (array $attributes, User $user) => [
                    'name' => $user->name.'\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                ])
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }

    /**
     * Indicate that the user is a student.
     */
    public function student(): static
    {
        return $this->state(fn () => ['role' => 'student']);
    }

    /**
     * Indicate that the user is a lecturer.
     */
    public function lecturer(): static
    {
        return $this->state(fn () => ['role' => 'lecturer']);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    /**
     * Create a student user together with a linked StudentProfile.
     *
     * Usage: User::factory()->withStudentProfile(['programme' => 'Engineering'])->create();
     */
    public function withStudentProfile(array $attributes = []): static
    {
        return $this->state(fn () => ['role' => 'student'])
            ->afterCreating(function (User $user) use ($attributes) {
                $user->studentProfile()->create(array_merge([
                    'student_number' => 'STU-'.fake()->unique()->numerify('####-####'),
                    'programme' => 'Computer Science',
                    'year' => 1,
                    'status' => 'active',
                ], $attributes));
            });
    }

    /**
     * Create a lecturer user together with a linked LecturerProfile.
     *
     * Usage: User::factory()->withLecturerProfile(['department' => 'Maths'])->create();
     */
    public function withLecturerProfile(array $attributes = []): static
    {
        return $this->state(fn () => ['role' => 'lecturer'])
            ->afterCreating(function (User $user) use ($attributes) {
                $user->lecturerProfile()->create(array_merge([
                    'staff_number' => 'LEC-'.fake()->unique()->numerify('####-####'),
                    'department' => 'Computer Science',
                    'status' => 'active',
                ], $attributes));
            });
    }
}