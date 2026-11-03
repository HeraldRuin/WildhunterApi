<?php

namespace Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Booking\Models\Payment;

class SbpPaymentController extends Controller
{
    public function show(string $code): Response
    {
        $payment = Payment::query()->where('code', $code)->first();
        $sbp = is_array($payment?->logs) ? ($payment->logs['sbp'] ?? null) : null;
        $image = is_array($sbp) ? trim((string) ($sbp['qr_code'] ?? '')) : '';

        if (
            !$payment
            || $payment->status !== Payment::STATUS_PROCESSING
            || !str_starts_with($image, 'data:image/')
        ) {
            abort(404);
        }

        $link = is_array($sbp) ? trim((string) ($sbp['qr_link'] ?? '')) : '';

        return response()
            ->view('Booking::sbp', [
                'image' => $image,
                'link' => str_starts_with($link, 'https://') ? $link : null,
            ])
            ->header('Content-Security-Policy', 'frame-ancestors *');
    }
}
