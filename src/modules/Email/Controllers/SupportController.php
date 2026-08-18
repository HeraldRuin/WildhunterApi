<?php

namespace Modules\Email\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Service\MailService;
use Illuminate\Http\JsonResponse;
use Modules\Email\Emails\SupportMessageEmail;
use Modules\Email\Http\Requests\SupportRequest;

class SupportController
{
    public function __construct(
        private readonly MailService $mailService,
    ) {
    }

    public function send(SupportRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->mailService->send((string) config('support.email'), new SupportMessageEmail(
            name: $data['name'],
            email: $data['email'],
            supportMessage: $data['message'],
        ));

        return new SuccessResponse(code: 'email_sent_successfully', domain: 'support');
    }
}
