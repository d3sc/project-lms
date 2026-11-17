<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guests_are_redirected_to_login_from_root(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_login_page_is_reachable(): void
    {
        $this->get('/login')->assertOk();
    }
}
