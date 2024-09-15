<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use ReusableTest;
    
    public function testRegisterSuccessUserAccount() {
        $data = [
            'firstName' => 'Test Name',
            'middleName' => 'Test',
            'lastName' => 'Admin',
            'email' => 'test@gmail.com',
            'password' => 'admin123'
        ];
        
        //register
        $response = $this->post('http://localhost:8000/api/register', $data);
        //assert
        $response->assertStatus(200);
        $this->assertTrue($response['data']['firstName'] == 'Test Name');

        //delete test data
        $user = User::where('email', 'test@gmail.com')->first();
        if ($user) {
            $user->delete();
        }
    }

    public function testRegisterDuplicateEmail() {
        $data1 = [
            'firstName' => 'Test Name1',
            'middleName' => 'Test1',
            'lastName' => 'Admin1',
            'email' => 'test@gmail.com',
            'password' => 'admin123'
        ];

        $data2 = [
            'firstName' => 'Test Name2',
            'middleName' => 'Test2',
            'lastName' => 'Admin2',
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
        $user = User::where('email', 'test@gmail.com')->first();
        if ($user) {
            $user->delete();
        }
    }

    public function testRegisterInvalidEmail() {
        $data = [
            'firstName' => 'Test Name',
            'middleName' => 'Test',
            'lastName' => 'Admin',
            'email' => 'test.com',
            'password' => 'admin123'
        ];
        
        //register
        $response = $this->post('http://localhost:8000/api/register', $data);
        //assert
        $response
        ->assertStatus(422)
        ->assertJson([
            'errors' => [
                'email' => [
                    'The email field must be a valid email address.'
                ],
            ]
        ]);
    }

    public function testRegisterNoEmail() {
        $data = [
            'firstName' => 'Test Name',
            'middleName' => 'Test',
            'lastName' => 'Admin',
            'email' => '',
            'password' => 'admin123'
        ];
        
        //register
        $response = $this->post('http://localhost:8000/api/register', $data);
        //assert
        $response
        ->assertStatus(422)
        ->assertJson([
            'errors' => [
                'email' => [
                    'The email field is required.'
                ],
            ]
        ]);
    }

    public function testRegisterNoPassword() {
        $data = [
            'firstName' => 'Test Name',
            'middleName' => 'Test',
            'lastName' => 'Admin',
            'email' => 'test@gmail.com',
            'password' => ''
        ];
        
        //register
        $response = $this->post('http://localhost:8000/api/register', $data);
        //assert
        $response
        ->assertStatus(422)
        ->assertJson([
            'errors' => [
                'password' => [
                    'The password field is required.'
                ],
            ]
        ]);
    }

}
