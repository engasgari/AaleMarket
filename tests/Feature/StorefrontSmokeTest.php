<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk()->assertSee('ورود');
    }

    public function test_products_page_loads(): void
    {
        $this->get('/products')->assertOk();
    }
}
