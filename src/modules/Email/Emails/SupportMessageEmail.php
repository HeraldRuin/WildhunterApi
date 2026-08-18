<?php

namespace Modules\Email\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportMessageEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $supportMessage,
    ) {
    }

    public function build(): self
    {
        return $this->from(
                (string) config('mail.from.address'),
                (string) config('mail.from.name')
            )
            ->subject('Новое сообщение из формы поддержки')
            ->replyTo($this->email, $this->name)
            ->view('Email::support-message')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'supportMessage' => $this->supportMessage,
            ]);
    }
}
