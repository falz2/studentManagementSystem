<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_portal_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_only_admins_can_access_admin_portal_routes(): void
    {
        $adminRoutes = [
            '/admin',
            '/admin/students',
            '/admin/courses',
            '/admin/lecturers',
            '/admin/registrations',
        ];

        foreach (['student', 'lecturer'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            foreach ($adminRoutes as $route) {
                $this->actingAs($user)->get($route)->assertForbidden();
            }
        }
    }

    public function test_student_and_lecturer_dashboards_require_the_matching_role(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lecturer = User::factory()->create(['role' => 'lecturer']);

        $this->actingAs($student)->get('/lecturer')->assertForbidden();
        $this->actingAs($lecturer)->get('/student')->assertForbidden();
    }

    public function test_admin_can_access_all_admin_portal_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            '/admin',
            '/admin/students',
            '/admin/courses',
            '/admin/lecturers',
            '/admin/registrations',
        ] as $route) {
            $this->actingAs($admin)->get($route)->assertOk();
        }
    }

    public function test_unknown_roles_are_denied_by_default(): void
    {
        $user = User::factory()->create(['role' => 'unknown']);

        $this->actingAs($user)->get('/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
