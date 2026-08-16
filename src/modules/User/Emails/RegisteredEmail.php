<?php

namespace Modules\User\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisteredEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $content,
        public string $emailType = 'customer',
    ) {
    }

    public function build(): self
    {
        $name = trim(($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? ''));
        if ($name === '') {
            $name = (string) ($this->user->user_name ?: $this->user->email ?: '');
        }

        return $this->subject(__('user.email.registered_subject', ['name' => $name]))
            ->view('User::emails.registered')
            ->with([
                'user' => $this->user,
                'content' => $this->content,
                'to' => $this->emailType,
            ]);
    }
}
