<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Response as FacadeResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use ReusableTest;

    public function testLoginSuccessAdmin() {
        //Admin User
        $user = User::factory()->create([
            'isAdmin' => true,
            'password' => Hash::make('lester123'),
        ]);
        $login = self::testLogin([
            'email' => $user['email'],
            'password' => 'lester123'
        ]);
        $login
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'data' => [
                    'isAdmin' => 1
                ]
            ]);

        $user->delete();
    }

    public function testLoginSuccessUser() {
        //Normal User
        $user = User::factory()->create([
            'isAdmin' => false,
            'password' => Hash::make('lester123'),
        ]);
        $login = self::testLogin([
            'email' => $user['email'],
            'password' => 'lester123'
        ]);
        $login
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'data' => [
                    'isAdmin' => 0
                ]
            ]);

        $user->delete();
    }

    public function testLoginInvalidEmail() {
        $credentials = [
            'email' => 'lester.com',
            'password' => 'lester123'
        ];
        $login = self::testLogin($credentials);
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
        $login = self::testLogin($credentials);
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
        $login = self::testLogin($credentials);
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
        $login = self::testLogin($credentials);
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
        $login = self::testLogin($credentials);
        $login
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Credentials',
            ]);
    }
}
