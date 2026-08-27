<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Modules\Email\Emails\SupportMessageEmail;
use Tests\TestCase;

class SupportEmailTest extends TestCase
{
    public function test_it_sends_support_email_to_fixed_recipient(): void
    {
        Mail::fake();

        config(['support.email' => 'support@example.com']);

        $response = $this->postJson('/api/v1/support', [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'message' => 'Нужна помощь с бронированием.',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Письмо успешно отправлено.',
                'data' => [],
            ]);

        Mail::assertSent(SupportMessageEmail::class, function (SupportMessageEmail $mail): bool {
            return $mail->hasTo('support@example.com')
                && $mail->name === 'Иван'
                && $mail->email === 'ivan@example.com'
                && $mail->supportMessage === 'Нужна помощь с бронированием.';
        });
    }
}
