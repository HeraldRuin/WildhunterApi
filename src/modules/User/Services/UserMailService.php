<?php

namespace Modules\User\Services;

use App\Models\User;
use App\Service\MailService;
use Illuminate\Support\Facades\Crypt;
use Modules\User\Emails\RegisteredEmail;

class UserMailService
{
    public function __construct(
        private readonly MailService $mailService,
    ) {
    }

    public function sendRegistered(User $user, ?string $plainPassword = null): void
    {
        $oldLocale = app()->getLocale();

        if (!empty($user->locale)) {
            app()->setLocale($user->locale);
        }

        $password = $plainPassword ?: $this->plainPassword($user);

        if ($this->settingEnabled('enable_mail_user_registered') && $user->email) {
            $this->mailService->send(
                $user->email,
                new RegisteredEmail(
                    $user,
                    $this->replaceContent($user, $password, $this->userBody()),
                    'customer',
                ),
            );
        }

        $adminEmail = setting_item('admin_email');

        if ($adminEmail && $this->settingEnabled('admin_enable_mail_user_registered')) {
            $this->mailService->send(
                $adminEmail,
                new RegisteredEmail(
                    $user,
                    $this->replaceContent($user, $password, $this->adminBody()),
                    'admin',
                ),
            );
        }

        app()->setLocale($oldLocale);
    }

    private function settingEnabled(string $key): bool
    {
        $value = setting_item($key);

        if ($value === '' || $value === null) {
            return true;
        }

        return $value === true || $value === 1 || $value === '1';
    }

    private function userBody(): string
    {
        $body = setting_item_with_lang('user_content_email_registered', app()->getLocale());

        return $body !== '' && $body !== null
            ? (string) $body
            : __('user.email.registered_user_body');
    }

    private function adminBody(): string
    {
        $body = setting_item_with_lang('admin_content_email_user_registered', app()->getLocale());

        return $body !== '' && $body !== null
            ? (string) $body
            : __('user.email.registered_admin_body');
    }

    private function replaceContent(User $user, ?string $password, string $content): string
    {
        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($name === '') {
            $name = (string) ($user->user_name ?: $user->email ?: '');
        }

        $replacements = [
            '[first_name]' => (string) ($user->first_name ?? ''),
            '[last_name]' => (string) ($user->last_name ?? ''),
            '[name]' => $name,
            '[email]' => (string) ($user->email ?? ''),
            '[login]' => (string) ($user->email ?? ''),
            '[password]' => (string) ($password ?? ''),
            '[button_verify]' => '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function plainPassword(User $user): ?string
    {
        if (empty($user->current_password)) {
            return null;
        }

        try {
            return Crypt::decryptString($user->current_password);
        } catch (\Throwable) {
            return null;
        }
    }
}
