<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\Support\PreparesAuthDatabase;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use PreparesAuthDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareAuthDatabase();
    }

    public function test_register_requires_profile_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error_code', 'validation_error')
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'role',
                'phone',
                'email',
                'password',
                'term',
            ]);
    }

    public function test_register_rejects_unknown_role(): void
    {
        $response = $this->postJson('/api/v1/register', $this->payload([
            'role' => 'admin',
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role'])
            ->assertJsonPath('errors.role.0', 'Недопустимая роль');
    }

    public function test_register_requires_accepted_terms(): void
    {
        $response = $this->postJson('/api/v1/register', $this->payload([
            'term' => false,
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['term'])
            ->assertJsonPath('errors.term.0', 'Необходимо принять условия');
    }

    public function test_register_rejects_weak_password(): void
    {
        $tooShort = $this->postJson('/api/v1/register', $this->payload([
            'password' => 'Ab1',
        ]));
        $tooShort
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        $withoutMixedCase = $this->postJson('/api/v1/register', $this->payload([
            'password' => 'secret123',
        ]));
        $withoutMixedCase
            ->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'Пароль должен содержать заглавные и строчные буквы');

        $withoutNumber = $this->postJson('/api/v1/register', $this->payload([
            'password' => 'SecretPassword',
        ]));
        $withoutNumber
            ->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'Пароль должен содержать хотя бы одну цифру');
    }

    public function test_register_rejects_duplicate_email_and_phone(): void
    {
        User::factory()->create([
            'email' => 'ivan@example.com',
            'phone' => '+79990001122',
        ]);

        $duplicateEmail = $this->postJson('/api/v1/register', $this->payload([
            'phone' => '+79990001123',
        ]));
        $duplicateEmail
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Этот email уже занят');

        $duplicatePhone = $this->postJson('/api/v1/register', $this->payload([
            'email' => 'other@example.com',
        ]));
        $duplicatePhone
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone'])
            ->assertJsonPath('errors.phone.0', 'Такой телефон уже существует');
    }

    public function test_register_rejects_compromised_password(): void
    {
        $password = 'Secret123';

        Http::fake(function ($request) use ($password) {
            $hash = strtoupper(sha1($password));
            $prefix = strtoupper(substr((string) $request->url(), -5));

            if (!str_starts_with($hash, $prefix)) {
                return Http::response('', 200);
            }

            return Http::response(substr($hash, 5).':3', 200);
        });

        $response = $this->postJson('/api/v1/register', $this->payload());

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Этот пароль найден в утечках данных. Выберите другой пароль.');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'role' => 'hunter',
            'phone' => '+79990001122',
            'email' => 'ivan@example.com',
            'password' => 'Secret123',
            'term' => true,
        ], $overrides);
    }
}
