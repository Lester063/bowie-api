<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Response as FacadeResponse;

class LoginTest extends TestCase
{
    use ReusableTest;

    public function testLoginSuccessAdmin() {
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
                    'isAdmin' => 1
                ]
            ]);
    }

    public function testLoginSuccessUser() {
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
                    'isAdmin' => 0
                ]
            ]);
    }

    public function testLoginInvalidEmail() {
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

    public function testLoginInvalidPassword() {
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

    public function testLoginNoEmail() {
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

    public function testLoginNoPassword() {
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

    public function testLoginNoCredentials() {
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
