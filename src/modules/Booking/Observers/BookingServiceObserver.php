<?php

namespace Modules\Booking\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\BookingService;
use Modules\Booking\Models\BookingServiceLog;

class BookingServiceObserver
{
    public function created(BookingService $service): void
    {
        $this->write($service, BookingServiceLog::ACTION_ADDED);
    }

    public function updated(BookingService $service): void
    {
        $this->write($service, BookingServiceLog::ACTION_UPDATED);
    }

    public function deleted(BookingService $service): void
    {
        $this->write($service, BookingServiceLog::ACTION_DELETED);
    }

    private function write(BookingService $service, string $action): void
    {
        if (!$service->booking_id) {
            return;
        }

        try {
            $actor = Auth::user();
            $hunter = $service->hunter_id ? $service->hunter : null;
            $animal = $service->animal_id ? $service->animal : null;
            $actorUser = $actor instanceof User ? $actor : null;

            BookingServiceLog::query()->create([
                'booking_id' => $service->booking_id,
                'booking_service_id' => $service->id,
                'action' => $action,
                'service_type' => $service->service_type,
                'service_id' => $service->service_id,
                'title' => $service->type,
                'calculation_type' => $service->calculation_type,
                'count' => $service->count,
                'price' => $service->price,
                'hunter_id' => $service->hunter_id,
                'hunter_name' => $this->personName($hunter),
                'animal_id' => $service->animal_id,
                'animal_title' => $animal?->title,
                'comment' => $service->comment,
                'place' => $service->getAttribute('place'),
                'user_id' => $actorUser?->id,
                'user_name' => $this->personName($actorUser),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Не записан журнал услуги брони', [
                'booking_id' => $service->booking_id,
                'booking_service_id' => $service->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function personName(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        $name = trim(implode(' ', array_filter([
            $user->first_name,
            $user->last_name,
        ])));

        if ($name !== '') {
            return $name;
        }

        $fallback = $user->user_name ?: $user->name;

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }
}
