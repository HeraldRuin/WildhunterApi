<?php

namespace Tests\Feature\Auth;

use App\Exceptions\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Modules\User\Dto\Auth\UpdatePasswordData;
use Modules\User\Services\Auth\PasswordService;
use Tests\Support\PreparesAuthDatabase;
use Tests\TestCase;

class PasswordServiceTest extends TestCase
{
    use PreparesAuthDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareAuthDatabase();
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    public function test_update_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'hunter@example.com',
            'password' => 'Secret123',
        ]);

        $service = app(PasswordService::class);

        try {
            $service->update($user, new UpdatePasswordData(
                current_password: 'Wrong123',
                new_password: 'NewSecret123',
                new_password_confirmation: 'NewSecret123',
            ));
            $this->fail('Смена пароля с неверным текущим паролем должна завершаться ошибкой.');
        } catch (ValidationException $exception) {
            $this->assertSame('current_password_incorrect', $exception->getErrorCode());
            $this->assertSame('password', $exception->getDomain());
            $this->assertSame(422, $exception->getStatus());
        }

        $user->refresh();
        $this->assertTrue(Hash::check('Secret123', $user->password));
    }

    public function test_reset_code_is_stored_and_blocked_for_90_seconds(): void
    {
        $this->freezeSecond();

        $service = app(PasswordService::class);
        $email = 'hunter@example.com';

        $first = $service->sendResetCode($email);

        $this->assertSame('code_send_successfully', $first['code']);
        $this->assertSame((int) config('auth.password_reset.ttl'), $first['ttl']);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $first['reset_code']);
        $this->assertSame($first['reset_code'], Cache::get('reset-password:'.$email));
        $this->assertTrue(Cache::has('reset-password-cooldown:'.$email));

        $this->assertResetCodeIsCoolingDown($service, $email);

        $this->travel(89)->seconds();
        $this->assertResetCodeIsCoolingDown($service, $email);

        $this->travel(1)->seconds();

        $second = $service->sendResetCode($email);

        $this->assertSame('code_send_successfully', $second['code']);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $second['reset_code']);
        $this->assertSame($second['reset_code'], Cache::get('reset-password:'.$email));
    }

    private function assertResetCodeIsCoolingDown(PasswordService $service, string $email): void
    {
        try {
            $service->sendResetCode($email);
            $this->fail('Повторная отправка кода должна быть недоступна 90 секунд.');
        } catch (ValidationException $exception) {
            $this->assertSame('code_already_sent', $exception->getErrorCode());
            $this->assertSame('password', $exception->getDomain());
        }
    }
}
