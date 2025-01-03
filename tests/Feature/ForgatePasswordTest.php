<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgatePasswordTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_check_email_not_exists(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "newhaidar@gmail.com"
            ],
        );
        $response->assertStatus(422); 
    }
    public function test_check_email_exists(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "haidar@gmail.com"
            ],
        );
        $response->assertStatus(200); 
    } 

    public function test_check_email_exists_and_code_sent_to_email(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "haidar@gmail.com"
            ],
        );
        $this->assertTrue(Cache::has("haidar@gmail.com"));
        $response->assertStatus(200); 
    } 

    public function test_check_email_exists_and_code_sent_to_email_and_user_try_to_send_new_code(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "haidar@gmail.com"
            ],
        );
        $this->assertTrue(Cache::has("haidar@gmail.com"));

        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "haidar@gmail.com"
            ],
        );
        $response->assertStatus(400); 
    } 

    public function test_check_code_expired(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "haidar@gmail.com"
            ],
        );
        $code =Cache::get("haidar@gmail.com");
        Cache::delete("haidar@gmail.com");
        
        $response = $this->post(
            "/api/checkCode",
            [
                'email' => "haidar@gmail.com",
                'code' =>$code
            ],
        );
        $response->assertStatus(400); 
    }

    public function test_check_code_incorrect(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "haidar@gmail.com"
            ],
        );
        $code =Cache::get("haidar@gmail.com");
        
        $response = $this->post(
            "/api/checkCode",
            [
                'email' => "haidar@gmail.com",
                'code' =>($code+1)
            ],
        );
        $response->assertStatus(400); 
    }

    public function test_check_code_correct(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => "haidar@gmail.com"
            ],
        );
        $code =Cache::get("haidar@gmail.com");
        
        $response = $this->post(
            "/api/checkCode",
            [
                'email' => "haidar@gmail.com",
                'code' =>$code
            ],
        );
        $response->assertStatus(200); 
    }

    public function test_check_password_weak_password(): void
    {
        User::create([
            'name'     => 'haidar',
            'email'    => 'haidar@gmail.com',
            'password' => Hash::make('123456789')
        ]);

        $response = $this->post(
            "/api/changePassword",
            [
                'email' => "haidar@gmail.com",
                'password' =>12345678
            ],
        );
        $response->assertStatus(422); 
     }

     public function test_check_password_valid_password(): void
     {
         User::create([
             'name'     => 'haidar',
             'email'    => 'haidar@gmail.com',
             'password' => Hash::make('123456789')
         ]);
 
         $response = $this->post(
             "/api/changePassword",
             [
                 'email' => "haidar@gmail.com",
                 'password' =>"Haidar12345678!!"
             ],
         );
         $response->assertStatus(200); 
      }
}
