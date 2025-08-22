<?php

namespace Tests\Feature\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\PasswordReset;

class PasswordResetControllerTest extends TestCase
{

    public function test_send_otp_success()
    {
        // assert user not found with validation error
        $userNotFound = User::factory()->make();

        $response = $this->postJson('/api/forgot-password', [
            'email' => $userNotFound->email
        ]);

        $response->assertUnprocessable()
            ->assertJson(['message' => 'The selected email is invalid.']);

        $this->assertDatabaseMissing('users', ['email' => $userNotFound->email]);

        // assert user found
        $user = User::factory()->create();
        $response = $this->postJson('/api/forgot-password', [
            'email' => $user->email
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'OTP sent to your email']);

        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    public function test_verify_otp_success()
    {
        $user = PasswordReset::factory()->create();

        $response = $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => $user->otp
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Otp verified']);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
            'otp' => $user->otp
        ]);
    }

    public function test_verify_expired_otp()
    {
        $user = PasswordReset::factory()->create(['expires_at' => now()->subMinutes(11)]);

        $response = $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => $user->otp
        ]);

        $response->assertBadRequest();
    }

    public function test_update_password_success()
    {
        $user = PasswordReset::factory()->create();

        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'otp' => $user->otp,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password reset successful']);
    }
}
