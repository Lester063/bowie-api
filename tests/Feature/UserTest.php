<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_login(): void
    {
        //Admin User
        $credentials = [
            'email' => 'lester@gmail.com',
            'password' => 'lester123'
        ];
        //login
        $login = $this->post('http://localhost:8000/api/login', $credentials);
        $login->assertStatus(200);
    }

    private function test_delete_user($id): void
    {
        $this->test_login();
        $delete = $this->delete('http://localhost:8000/api/user/'.$id.'/delete');
        $delete->assertStatus(200);
    }

    public function test_register(): void
    {
        $data = [
            'first_name' => 'Test Name',
            'middle_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'test@gmail.com',
            'password' => 'admin123'
        ];
        
        //register
        $response = $this->post('http://localhost:8000/api/register', $data);
        //assert
        $response->assertStatus(200);
        $this->assertTrue($response['data']['first_name'] == 'Test Name');

        //delete test data
        $this->test_delete_user($response['data']['id']);
    }

    public function test_duplicate_email_validation(): void
    {
        $data1 = [
            'first_name' => 'Test Name1',
            'middle_name' => 'Test1',
            'last_name' => 'Admin1',
            'email' => 'test@gmail.com',
            'password' => 'admin123'
        ];

        $data2 = [
            'first_name' => 'Test Name2',
            'middle_name' => 'Test2',
            'last_name' => 'Admin2',
            'email' => 'test@gmail.com',
            'password' => 'admin123'
        ];
        
        //create first user
        $user1 = $this->post('http://localhost:8000/api/register', $data1);
        $user1->assertStatus(200);
        
        //create second user, User should not be created because of duplicated email validation
        $user2 = $this->post('http://localhost:8000/api/register', $data2);
        $user2->assertStatus(422);

        //delete test data
        $this->test_delete_user($user1['data']['id']);
    }

}
