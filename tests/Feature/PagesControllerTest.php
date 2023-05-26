<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PagesControllerTest extends TestCase
{
    /**
     * A guest could visit and get the signup page and 
     *
     * @test
     */
    public function it_returns_register_page()
    {
        $response = $this->get(route('register'));
        $response->assertViewIs('auth.register');
        $response->assertSuccessful();
    }
    
    /**
     * A guest could visit and get the login page.
     *
     * @test
     */
    public function it_returns_login_page()
    {
        $response = $this->get(route('login'));
        $response->assertViewIs('auth.login');
        $response->assertSuccessful();
    }
}
