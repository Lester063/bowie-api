<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Response as FacadeResponse;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    private function test_login($credentials)
    {
        $login = $this->post('http://localhost:8000/api/login', $credentials);
        return $login;
    }

    public function test_login_success_admin(): void
    {
        //Admin User
        $credentials = [
            'email' => 'lester@gmail.com',
            'password' => 'lester123'
        ];
        $login = $this->test_login($credentials);
        $login
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'data' => [
                    'is_admin' => 1
                ]
            ]);
    }

    public function test_login_success_user(): void
    {
        //Normal User
        $credentials = [
            'email' => 'jerome@gmail.com',
            'password' => 'lester123'
        ];
        $login = $this->test_login($credentials);
        $login
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'data' => [
                    'is_admin' => 0
                ]
            ]);
    }

    public function test_login_invalid_email(): void
    {
        $credentials = [
            'email' => 'lester.com',
            'password' => 'lester123'
        ];
        $login = $this->test_login($credentials);
        $login
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Credentials',
            ]);
    }

    public function test_login_invalid_password(): void
    {
        $credentials = [
            'email' => 'lester@gmail.com',
            'password' => 'lester1234'
        ];
        $login = $this->test_login($credentials);
        $login
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Credentials',
            ]);
    }

    public function test_login_no_email(): void
    {
        $credentials = [
            'email' => '',
            'password' => 'lester1234'
        ];
        $login = $this->test_login($credentials);
        $login
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Credentials',
            ]);
    }

    public function test_login_no_password(): void
    {
        $credentials = [
            'email' => 'lester@gmail.com',
            'password' => ''
        ];
        $login = $this->test_login($credentials);
        $login
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Credentials',
            ]);
    }

    public function test_login_no_credentials(): void
    {
        $credentials = [
            'email' => '',
            'password' => ''
        ];
        $login = $this->test_login($credentials);
        $login
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Credentials',
            ]);
    }
}
