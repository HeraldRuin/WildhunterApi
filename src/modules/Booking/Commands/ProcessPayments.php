<?php

namespace Modules\Booking\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\Booking\Models\Payment;
use Modules\Booking\Services\PaymentService;

class ProcessPayments extends Command
{
    protected $signature = 'payments:process';
    protected $description = 'Poll due PayKeeper prepayments';

    public function handle(PaymentService $service): int
    {
        $lock = Cache::lock('payments:process', 55);

        if (!$lock->get()) {
            return self::SUCCESS;
        }

        try {
            Payment::query()
                ->due()
                ->orderBy('id')
                ->chunkById(100, function ($payments) use ($service): void {
                    foreach ($payments as $payment) {
                        $service->poll($payment);
                    }
                });

            $service->reconcilePrepaymentCollections();
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }
}
