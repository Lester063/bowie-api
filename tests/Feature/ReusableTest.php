<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker;
// use Tests\TestCase;

trait ReusableTest
{
    private function test_login($credentials) {
        $login = $this->post('http://localhost:8000/api/login', $credentials);
        return $login;
    }
}
