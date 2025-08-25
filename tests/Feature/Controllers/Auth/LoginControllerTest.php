<?php

namespace Tests\Feature\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;

class LoginControllerTest extends TestCase
{
    /**
     * Test successful login.
     *
     * @return void
     */
    public function test_successful_login(): void
    {
        // Create an user user
        $user = User::factory()->create([
            'email' => fake()->safeEmail(),
            'password' => bcrypt('password'),
        ]);

        // Make a login request with valid credentials
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password', // assuming the default password is 'password'
        ]);

        // Assert the response is successful and contains the expected data
        $response->assertOk()
            ->assertJsonStructure([
                'access_token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    /**
     * Test failed login with invalid credentials.
     *
     * @return void
     */
    public function test_failed_login_with_invalid_credentials(): void
    {
        // Make a login request with invalid credentials
        $response = $this->postJson('/api/login', [
            'email' => fake()->safeEmail(),
            'password' => fake()->password(),
        ]);

        // Assert the response is unauthorized and contains the expected error message
        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthorized']);
    }

    /**
     * Test successful login.
     *
     * @return void
     */
    public function test_successful_get_user_info(): void
    {
        // Create an user user
        $user = User::factory()->create([
            'email' => fake()->safeEmail(),
            'password' => 'password',
        ]);

        $token = $user->createToken('api', ['transfers:create'])->plainTextToken;

        // Make a login request with valid credentials
        $response = $this->getJson(
            '/api/me',
            headers: ['Authorization' => 'Bearer ' . $token]
        );

        // Assert the response is successful and contains the expected data
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ]
            ]);
    }
}
