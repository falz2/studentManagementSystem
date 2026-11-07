<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        User::query()->orderBy('id')->eachById(function (User $user): void {
            $role = Role::findOrCreate($user->role ?: 'student', 'web');
            $user->syncRoles($role);
        });

        foreach (['admin', 'lecturer', 'student'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('student');
        });

        DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->select('model_has_roles.model_id', 'roles.name')
            ->orderBy('model_has_roles.model_id')
            ->get()
            ->each(function (object $assignment): void {
                DB::table('users')
                    ->where('id', $assignment->model_id)
                    ->update(['role' => $assignment->name]);
            });
    }
};
