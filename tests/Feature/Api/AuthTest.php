<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function postWithApiKey($uri, $data = [])
    {
        return $this->withHeader('x-api-key', config('app.mix_api_key'))->postJson($uri, $data);
    }

    protected function getWithApiKey($uri)
    {
        return $this->withHeader('x-api-key', config('app.mix_api_key'))->getJson($uri);
    }

    /** @test */
    public function can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test_admin@example.com',
            'password' => bcrypt('password123'),
            'status' => 5 // Status::ACTIVE is 5 based on Enums\Status
        ]);

        $response = $this->postWithApiKey('/api/auth/login', [
            'email'    => 'test_admin@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email'],
            'message'
        ]);
    }

    /** @test */
    public function cannot_login_with_invalid_credentials()
    {
        $response = $this->postWithApiKey('/api/auth/login', [
            'email'    => 'wrong@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['errors' => ['validation']]);
    }

    /** @test */
    public function authenticated_user_can_access_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'x-api-key' => config('app.mix_api_key')
        ])->getJson('/api/profile');

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $user->email]);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'x-api-key' => config('app.mix_api_key')
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $this->assertCount(0, $user->fresh()->tokens);
    }
}
