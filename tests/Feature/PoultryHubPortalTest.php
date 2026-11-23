<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoultryHubPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_directed_to_login_and_registration_offers_poultryhub_roles(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/marketplace')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Welcome back');
        $this->get('/register')
            ->assertOk()
            ->assertSee('Farmer')
            ->assertSee('Feed supplier')
            ->assertSee('Consumer');
    }

    public function test_dashboard_redirects_users_to_their_role_workspace(): void
    {
        $farmer = User::factory()->farmer()->create();
        $supplier = User::factory()->supplier()->create();
        $consumer = User::factory()->consumer()->create();
        $legacyStudent = User::factory()->student()->create();
        $legacyLecturer = User::factory()->lecturer()->create();

        $this->actingAs($farmer)->get('/dashboard')->assertRedirect('/seller');
        $this->actingAs($supplier)->get('/dashboard')->assertRedirect('/seller');
        $this->actingAs($consumer)->get('/dashboard')->assertRedirect('/marketplace');
        $this->actingAs($legacyStudent)->get('/dashboard')->assertRedirect('/marketplace');
        $this->actingAs($legacyLecturer)->get('/dashboard')->assertRedirect('/marketplace');
    }

    public function test_administrator_can_view_the_platform_overview(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Platform overview')
            ->assertSee('Marketplace orders');
    }
}
