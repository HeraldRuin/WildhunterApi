<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function send($to, $mailable): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            $traceId = request()->attributes->get('trace_id');

            Log::warning('Mail send failed', [
                'to' => $to,
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
