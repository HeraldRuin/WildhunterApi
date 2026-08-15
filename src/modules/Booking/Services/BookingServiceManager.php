<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\User;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalFine;
use Modules\Animals\Models\AnimalPreparation;
use Modules\Animals\Models\AnimalTrophy;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Dto\StoreAdditionalData;
use Modules\Booking\Dto\StoreFoodData;
use Modules\Booking\Dto\StorePenaltyData;
use Modules\Booking\Dto\StorePreparationData;
use Modules\Booking\Dto\StoreSpendingData;
use Modules\Booking\Dto\StoreTrophyData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\BookingService;
use Modules\Role\Models\Role;

class BookingServiceManager
{
    /**
     * @return array{
     *     role: string,
     *     booking_type: string,
     *     allowed_types: list<string>,
     *     catalogs: array<string, mixed>,
     *     items: array<string, mixed>
     * }
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function getServices(string $code, User $user): array
    {
        [$booking, $isAdmin, $allowedTypes] = $this->findAuthorizedBooking($code, $user);

        return [
            'role' => $isAdmin ? Role::ADMIN : Role::CUSTOMER,
            'booking_type' => $booking->type,
            'allowed_types' => $allowedTypes,
            'catalogs' => $this->catalogsFor($booking, $allowedTypes),
            'items' => $this->savedItems($booking),
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function createTrophy(string $code, StoreTrophyData $data, User $user): array
    {
        [$booking] = $this->findAuthorizedBooking($code, $user, AddetionalPrice::TROPHY);

        $trophy = AnimalTrophy::query()
            ->whereKey($data->trophyId)
            ->where('animal_id', $data->animalId)
            ->whereHas('hotelPrices', fn ($q) => $q->where('hotel_id', $booking->hotel_id))
            ->first();

        if (!$trophy) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $price = $trophy->hotelPrices()->where('hotel_id', $booking->hotel_id)->value('price');
        if ($price === null) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $service = BookingService::create([
            'booking_id' => $booking->id,
            'service_type' => AddetionalPrice::TROPHY,
            'type' => $data->type,
            'service_id' => null,
            'animal_id' => $data->animalId,
            'count' => $data->count,
            'price' => round((float) $price * $data->count, 2),
        ])->load('animal');

        return [
            'id' => $service->id,
            'animal_id' => $service->animal_id,
            'animal_title' => $service->animal->title ?? '—',
            'type' => $service->type,
            'count' => $service->count,
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createPenalty(string $code, StorePenaltyData $data, User $user): array
    {
        [$booking] = $this->findAuthorizedBooking($code, $user, AddetionalPrice::PENALTY);
        $this->assertHunterInBooking($booking, $data->hunterId);

        $penalty = AnimalFine::query()
            ->whereKey($data->penaltyId)
            ->where('animal_id', $data->animalId)
            ->whereHas('hotelPrices', fn ($q) => $q->where('hotel_id', $booking->hotel_id))
            ->first();

        if (!$penalty) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $price = $penalty->hotelPrices()->where('hotel_id', $booking->hotel_id)->value('price');
        if ($price === null) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $service = BookingService::create([
            'booking_id' => $booking->id,
            'service_type' => AddetionalPrice::PENALTY,
            'type' => $data->type,
            'service_id' => null,
            'hunter_id' => $data->hunterId,
            'animal_id' => $data->animalId,
            'price' => $price,
        ])->load(['hunter', 'animal']);

        return [
            'id' => $service->id,
            'animal_id' => $service->animal_id,
            'animal_title' => $service->animal->title ?? '—',
            'type' => $service->type,
            'count' => 1,
            'hunter_id' => $service->hunter_id,
            'hunter_name' => $this->hunterName($service->hunter),
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function createOrUpdatePreparation(string $code, StorePreparationData $data, User $user): array
    {
        [$booking] = $this->findAuthorizedBooking($code, $user, AddetionalPrice::PREPARATION);

        $preparation = AnimalPreparation::query()
            ->whereKey($data->preparationId)
            ->where('animal_id', $data->animalId)
            ->whereHas('hotelPrices', fn ($q) => $q->where('hotel_id', $booking->hotel_id))
            ->first();

        if (!$preparation) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $price = $preparation->hotelPrices()->where('hotel_id', $booking->hotel_id)->value('price');
        if ($price === null) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $totalCost = (float) $price * $data->count;

        $service = BookingService::query()
            ->where('booking_id', $booking->id)
            ->where('service_type', AddetionalPrice::PREPARATION)
            ->where('animal_id', $data->animalId)
            ->first();

        if ($service) {
            $service->count += $data->count;
            $service->price = round((float) $service->price + $totalCost, 2);
            $service->save();
            $service->load('animal');
        } else {
            $service = BookingService::create([
                'booking_id' => $booking->id,
                'service_type' => AddetionalPrice::PREPARATION,
                'type' => null,
                'service_id' => null,
                'animal_id' => $data->animalId,
                'count' => $data->count,
                'price' => round($totalCost, 2),
            ])->load('animal');
        }

        return [
            'id' => $service->id,
            'animal_id' => $service->animal_id,
            'animal_title' => $service->animal->title ?? '—',
            'count' => $service->count,
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function createFood(string $code, StoreFoodData $data, User $user): array
    {
        [$booking] = $this->findAuthorizedBooking($code, $user, AddetionalPrice::FOOD);

        $price = AddetionalPrice::query()
            ->where('type', AddetionalPrice::FOOD)
            ->where('hotel_id', $booking->hotel_id)
            ->value('price');

        if ($price === null) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $service = BookingService::create([
            'booking_id' => $booking->id,
            'service_type' => AddetionalPrice::FOOD,
            'type' => 'Питание',
            'price' => round((float) $price * $data->count, 2),
            'count' => $data->count,
        ]);

        return [
            'id' => $service->id,
            'type' => $service->type,
            'count' => $service->count,
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createAdditional(string $code, StoreAdditionalData $data, User $user): array
    {
        [$booking] = $this->findAuthorizedBooking($code, $user, AddetionalPrice::ADDETIONAL);

        $additional = AddetionalPrice::query()
            ->whereKey($data->additionalId)
            ->where('hotel_id', $booking->hotel_id)
            ->whereNull('type')
            ->where('price', '>', 0)
            ->first();

        if (!$additional) {
            throw new NotFoundException(
                errorCode: 'service_price_not_found',
                domain: 'booking',
            );
        }

        $hunterId = null;
        if ($additional->calculation_type === AddetionalPrice::INDIVIDUAL) {
            if (!$data->hunterId) {
                throw new ValidationException(
                    message: __('booking.validation.hunter_id_required'),
                    errorCode: 'hunter_id_required',
                    domain: 'booking',
                );
            }
            $this->assertHunterInBooking($booking, $data->hunterId);
            $hunterId = $data->hunterId;
        }

        $service = BookingService::create([
            'booking_id' => $booking->id,
            'service_type' => AddetionalPrice::ADDETIONAL,
            'type' => $data->name,
            'calculation_type' => $additional->calculation_type,
            'count' => $data->count,
            'hunter_id' => $hunterId,
            'price' => round((float) $additional->price * $data->count, 2),
        ])->load('hunter');

        return [
            'id' => $service->id,
            'type' => $service->type,
            'calculation_type' => $service->calculation_type,
            'count' => $service->count,
            'hunter_id' => $service->hunter_id,
            'hunter_name' => $this->hunterName($service->hunter),
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createSpending(string $code, StoreSpendingData $data, User $user): array
    {
        [$booking] = $this->findAuthorizedBooking($code, $user, AddetionalPrice::SPENDING);
        $this->assertHunterInBooking($booking, $data->hunterId);

        $service = BookingService::create([
            'booking_id' => $booking->id,
            'service_type' => AddetionalPrice::SPENDING,
            'price' => $data->price,
            'comment' => $data->comment,
            'service_id' => null,
            'hunter_id' => $data->hunterId,
        ])->load('hunter');

        return [
            'id' => $service->id,
            'price' => $service->price,
            'comment' => $service->comment,
            'hunter_id' => $service->hunter_id,
            'hunter_name' => $this->hunterName($service->hunter),
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function deleteService(string $code, int $serviceId, User $user): void
    {
        [$booking, , $allowedTypes] = $this->findAuthorizedBooking($code, $user);

        $service = BookingService::query()
            ->where('id', $serviceId)
            ->where('booking_id', $booking->id)
            ->first();

        if (!$service) {
            throw new NotFoundException(
                errorCode: 'service_not_found',
                domain: 'booking',
            );
        }

        if (!in_array($service->service_type, $allowedTypes, true)) {
            throw new ForbiddenException(
                errorCode: 'service_type_not_allowed',
                domain: 'booking',
            );
        }

        $service->delete();
    }

    /**
     * @return array{0: Booking, 1: bool, 2: list<string>}
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    private function findAuthorizedBooking(string $code, User $user, ?string $serviceType = null): array
    {
        $booking = Booking::query()->where('code', $code)->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $isAdmin = $user->hasRole(Role::ADMIN);

        if ($isAdmin) {
            $hotelIds = $user->hotels()->pluck('id');
            if ($hotelIds->isEmpty() || !$hotelIds->contains($booking->hotel_id)) {
                throw new ForbiddenException(
                    errorCode: 'booking_access_denied',
                    domain: 'booking',
                );
            }
        } else {
            $masterHunter = $booking->masterHunter()
                ->where('invited_by', $user->id)
                ->first();

            if (!$masterHunter) {
                throw new ForbiddenException(
                    errorCode: 'booking_access_denied',
                    domain: 'booking',
                );
            }
        }

        if (!in_array($booking->status, $this->allowedStatuses($booking, $isAdmin), true)) {
            throw new ConflictException(
                errorCode: 'add_services_not_available',
                domain: 'booking',
            );
        }

        $allowedTypes = $this->allowedTypes($booking, $isAdmin);

        if ($serviceType !== null && !in_array($serviceType, $allowedTypes, true)) {
            throw new ForbiddenException(
                errorCode: 'service_type_not_allowed',
                domain: 'booking',
            );
        }

        return [$booking, $isAdmin, $allowedTypes];
    }

    /**
     * @return list<string>
     */
    private function allowedStatuses(Booking $booking, bool $isAdmin): array
    {
        if ($booking->type === Booking::BookingTypeAnimal) {
            return [Booking::FINISHED_COLLECTION];
        }

        if ($isAdmin) {
            return [
                Booking::PREPAYMENT_COLLECTION,
                Booking::FINISHED_PREPAYMENT,
                Booking::BED_COLLECTION,
                Booking::FINISHED_BED,
            ];
        }

        return [
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ];
    }

    /**
     * @return list<string>
     */
    private function allowedTypes(Booking $booking, bool $isAdmin): array
    {
        $isHunting = $booking->type !== Booking::BookingTypeHotel;

        if ($isAdmin) {
            $types = [];
            if ($isHunting) {
                $types[] = AddetionalPrice::TROPHY;
                $types[] = AddetionalPrice::PENALTY;
                $types[] = AddetionalPrice::PREPARATION;
            }
            $types[] = AddetionalPrice::FOOD;
            $types[] = AddetionalPrice::ADDETIONAL;

            return $types;
        }

        $types = [];
        if ($isHunting) {
            $types[] = AddetionalPrice::PREPARATION;
        }
        $types[] = AddetionalPrice::FOOD;
        $types[] = AddetionalPrice::ADDETIONAL;
        $types[] = AddetionalPrice::SPENDING;

        return $types;
    }

    /**
     * @param  list<string>  $allowedTypes
     * @return array<string, mixed>
     */
    private function catalogsFor(Booking $booking, array $allowedTypes): array
    {
        $needsHunters = array_intersect($allowedTypes, [
            AddetionalPrice::PENALTY,
            AddetionalPrice::ADDETIONAL,
            AddetionalPrice::SPENDING,
        ]) !== [];

        return [
            'trophy_animals' => in_array(AddetionalPrice::TROPHY, $allowedTypes, true)
                ? $this->mapAnimalsWithService($booking, Animal::SERVICE_TROPHIES)
                : [],
            'penalty_animals' => in_array(AddetionalPrice::PENALTY, $allowedTypes, true)
                ? $this->mapAnimalsWithService($booking, Animal::SERVICE_FINES)
                : [],
            'preparation_animals' => in_array(AddetionalPrice::PREPARATION, $allowedTypes, true)
                ? $this->mapAnimalsWithService($booking, Animal::SERVICE_PREPARATIONS)
                : [],
            'hunters' => $needsHunters ? $this->acceptedHunters($booking) : [],
            'additionals' => in_array(AddetionalPrice::ADDETIONAL, $allowedTypes, true)
                ? $this->additionalCatalog($booking)
                : [],
        ];
    }

    /**
     * @return list<array{id: int, title: string, trophies?: mixed, fines?: mixed, preparations?: mixed}>
     */
    private function mapAnimalsWithService(Booking $booking, string $relation): array
    {
        return Animal::forHotelWithService($booking->hotel_id, $relation)
            ->get()
            ->map(fn (Animal $animal) => [
                'id' => $animal->id,
                'title' => $animal->title,
                $relation => $animal->{$relation}
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'type' => $item->type,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function acceptedHunters(Booking $booking): array
    {
        $masterId = $booking->masterHunterRowId();
        if (!$masterId) {
            return [];
        }

        $hunterIds = BookingHunterInvitation::query()
            ->where('booking_hunter_id', $masterId)
            ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
            ->pluck('hunter_id')
            ->unique()
            ->filter()
            ->all();

        if ($hunterIds === []) {
            return [];
        }

        return User::query()
            ->whereIn('id', $hunterIds)
            ->get(['id', 'name', 'first_name', 'last_name', 'user_name'])
            ->map(fn (User $hunter) => [
                'id' => $hunter->id,
                'name' => $this->hunterName($hunter),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, calculation_type: string|null, count: mixed, price: mixed}>
     */
    private function additionalCatalog(Booking $booking): array
    {
        return AddetionalPrice::query()
            ->whereNull('type')
            ->where('hotel_id', $booking->hotel_id)
            ->where('price', '>', 0)
            ->get()
            ->map(fn (AddetionalPrice $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'calculation_type' => $item->calculation_type,
                'count' => $item->count,
                'price' => $item->price,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     trophies: mixed,
     *     penalties: mixed,
     *     preparations: mixed,
     *     foods: mixed,
     *     additionals: mixed,
     *     spendings: mixed
     * }
     */
    private function savedItems(Booking $booking): array
    {
        $services = BookingService::with(['hunter', 'animal'])
            ->where('booking_id', $booking->id)
            ->get();

        return [
            'trophies' => $services
                ->where('service_type', AddetionalPrice::TROPHY)
                ->values()
                ->map(fn (BookingService $service) => [
                    'id' => $service->id,
                    'animal_id' => $service->animal_id,
                    'animal_title' => $service->animal->title ?? '—',
                    'type' => $service->type,
                    'count' => $service->count,
                ])
                ->all(),
            'penalties' => $services
                ->where('service_type', AddetionalPrice::PENALTY)
                ->values()
                ->map(fn (BookingService $service) => [
                    'id' => $service->id,
                    'animal_id' => $service->animal_id,
                    'animal_title' => $service->animal->title ?? '—',
                    'type' => $service->type,
                    'count' => 1,
                    'hunter_id' => $service->hunter_id,
                    'hunter_name' => $this->hunterName($service->hunter),
                ])
                ->all(),
            'preparations' => $services
                ->where('service_type', AddetionalPrice::PREPARATION)
                ->values()
                ->map(fn (BookingService $service) => [
                    'id' => $service->id,
                    'animal_id' => $service->animal_id,
                    'animal_title' => $service->animal->title ?? '—',
                    'count' => $service->count,
                ])
                ->all(),
            'foods' => $services
                ->where('service_type', AddetionalPrice::FOOD)
                ->values()
                ->map(fn (BookingService $service) => [
                    'id' => $service->id,
                    'type' => $service->type,
                    'count' => $service->count,
                ])
                ->all(),
            'additionals' => $services
                ->where('service_type', AddetionalPrice::ADDETIONAL)
                ->values()
                ->map(fn (BookingService $service) => [
                    'id' => $service->id,
                    'type' => $service->type,
                    'calculation_type' => $service->calculation_type,
                    'count' => $service->count,
                    'hunter_id' => $service->hunter_id,
                    'hunter_name' => $this->hunterName($service->hunter),
                ])
                ->all(),
            'spendings' => $services
                ->where('service_type', AddetionalPrice::SPENDING)
                ->values()
                ->map(fn (BookingService $service) => [
                    'id' => $service->id,
                    'price' => $service->price,
                    'comment' => $service->comment,
                    'hunter_id' => $service->hunter_id,
                    'hunter_name' => $this->hunterName($service->hunter),
                ])
                ->all(),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function assertHunterInBooking(Booking $booking, int $hunterId): void
    {
        $masterId = $booking->masterHunterRowId();
        $exists = $masterId && BookingHunterInvitation::query()
            ->where('booking_hunter_id', $masterId)
            ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
            ->where('hunter_id', $hunterId)
            ->exists();

        if (!$exists) {
            throw new ValidationException(
                message: __('booking.errors.user_not_found'),
                errorCode: 'user_not_found',
                domain: 'booking',
            );
        }
    }

    private function hunterName(?User $hunter): string
    {
        if (!$hunter) {
            return '—';
        }

        $name = trim(implode(' ', array_filter([
            $hunter->first_name,
            $hunter->last_name,
        ])));

        if ($name !== '') {
            return $name;
        }

        return $hunter->user_name ?: ($hunter->name ?: '—');
    }
}
