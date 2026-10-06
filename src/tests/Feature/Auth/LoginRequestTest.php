<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error_code', 'validation_error')
            ->assertJsonValidationErrors(['email', 'password'])
            ->assertJsonPath('errors.email.0', 'Email обязателен')
            ->assertJsonPath('errors.password.0', 'Пароль обязателен');
    }

    public function test_login_rejects_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
            'password' => 'Secret123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Неверный формат email');
    }

    public function test_login_rejects_password_shorter_than_6_characters(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'hunter@example.com',
            'password' => '12345',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Пароль должен быть минимум 6 символов');
    }
}
