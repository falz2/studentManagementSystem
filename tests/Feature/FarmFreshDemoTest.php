<?php

namespace Tests\Feature;

use Tests\TestCase;

class FarmFreshDemoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_demo_is_available_without_an_account_and_mounts_the_react_app(): void
    {
        $this->get('/farmfresh-demo')
            ->assertOk()
            ->assertSee('FarmFresh · Local poultry, made easy')
            ->assertSee('farmfresh-app', false);
    }
}
