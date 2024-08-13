<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use ReusableTest;
    
    public function testRegisterSuccessUserAccount() {
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
        $this->testDeleteUser($response['data']['id']);
    }

    public function testRegisterDuplicateEmail() {
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
        $this->testDeleteUser($user1['data']['id']);
    }

    public function testRegisterInvalidEmail() {
        $data = [
            'first_name' => 'Test Name',
            'middle_name' => 'Test',
            'last_name' => 'Admin',
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
            'first_name' => 'Test Name',
            'middle_name' => 'Test',
            'last_name' => 'Admin',
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
            'first_name' => 'Test Name',
            'middle_name' => 'Test',
            'last_name' => 'Admin',
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

    //use to cleanup data used on testing
    private function testDeleteUser($id) {
        //Admin User
        $credentials = [
            'email' => 'lester@gmail.com',
            'password' => 'lester123'
        ];
        $this->test_login($credentials);
        //$login = $this->post('http://localhost:8000/api/login', $credentials);
        $delete = $this->delete('http://localhost:8000/api/user/'.$id.'/delete');
        $delete->assertStatus(200);
    }

}
