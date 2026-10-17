<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Role\Models\Role;
use Tests\Support\PreparesAuthDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use PreparesAuthDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareAuthDatabase();
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'hunter@example.com',
            'password' => 'Secret123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'hunter@example.com',
            'password' => 'Wrong123',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'auth_invalid_credentials');
    }

    public function test_login_rejects_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'missing@example.com',
            'password' => 'Secret123',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('error_code', 'auth_invalid_credentials');
    }

    public function test_login_returns_user_and_token(): void
    {
        $user = User::factory()->create([
            'email' => 'hunter@example.com',
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'password' => 'Secret123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'hunter@example.com',
            'password' => 'Secret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'hunter@example.com')
            ->assertJsonPath('user.id', $user->id);

        $token = $response->json('token');
        $this->assertIsString($token);
        $this->assertNotSame('', $token);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'api_token',
        ]);
    }

    public function test_register_is_forbidden_when_registration_disabled(): void
    {
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response('', 200),
        ]);

        DB::table('core_settings')->insert([
            'name' => 'user_disable_register',
            'val' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/register', $this->validRegisterPayload());

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'registration_disabled');

        $this->assertDatabaseMissing('users', [
            'email' => 'ivan@example.com',
        ]);
    }

    public function test_register_creates_draft_hotel_from_base_name(): void
    {
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response('', 200),
        ]);

        Role::query()->create([
            'name' => 'Администратор базы',
            'code' => 'baseadmin',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'role' => 'baseadmin',
            'base_name' => 'Охотбаза Север',
            'phone' => '+79990001122',
            'email' => 'admin@example.com',
            'password' => 'Secret123',
            'term' => true,
        ]);

        $response->assertOk();

        $userId = User::query()->where('email', 'admin@example.com')->value('id');

        $this->assertDatabaseHas('bc_hotels', [
            'title' => 'Охотбаза Север',
            'status' => 'draft',
            'admin_base' => $userId,
            'author_id' => $userId,
            'create_user' => $userId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validRegisterPayload(): array
    {
        return [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'base_name' => 'Охотбаза Север',
            'role' => 'hunter',
            'phone' => '+79990001122',
            'email' => 'ivan@example.com',
            'password' => 'Secret123',
            'term' => true,
        ];
    }
}
