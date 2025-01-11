<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgatePasswordTest extends TestCase
{
    use DatabaseTransactions;
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
        $email = 'haidar' . random_int(1, 10) . '@gmail.com';

        User::create([
            'name'     => 'haidar',
            'email'    => $email,
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' => $email
            ],
        );
        $response->assertStatus(200);
    }

    public function test_check_email_exists_and_code_sent_to_email(): void
    {
        $email = 'haidar' . random_int(1, 10) . '@gmail.com';

        User::create([
            'name'     => 'haidar',
            'email'    =>   $email,
            'password' => Hash::make('123456789')
        ]);
        $response = $this->post(
            "/api/checkEmail",
            [
                'email' =>  $email
            ],
        );
        $this->assertTrue(Cache::has($email));
        Cache::clear();
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
        Cache::clear();
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
        $code = Cache::get("haidar@gmail.com");
        Cache::delete("haidar@gmail.com");

        $response = $this->post(
            "/api/checkCode",
            [
                'email' => "haidar@gmail.com",
                'code' => strval($code)
            ],
        );
        Cache::clear();
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
        $code = Cache::get("haidar@gmail.com");

        $response = $this->post(
            "/api/checkCode",
            [
                'email' => "haidar@gmail.com",
                'code' => strval($code + 1)
            ],
        );
        Cache::clear();
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
        $code = Cache::get("haidar@gmail.com");

        $response = $this->post(
            "/api/checkCode",
            [
                'email' => "haidar@gmail.com",
                'code' => strval($code)
            ],
        );
        $response->assertStatus(200);
        Cache::clear();
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
                'password' => 12345678
            ],
        );
        Cache::clear();
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
                'password' => "Haidar12345678!!"
            ],
        );
        Cache::clear();
        $response->assertStatus(200);
    }
}
