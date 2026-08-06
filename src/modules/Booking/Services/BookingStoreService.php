  <?php

namespace Modules\Booking\Services;

use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Animals\Models\AnimalPricePeriod;
use Modules\Booking\Dto\CreateBookingData;
use Modules\Booking\Models\BookedDay;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelRoomBooking;
use Modules\Hotel\Services\RoomService;

class BookingStoreService
{
    public function __construct(
        private RoomService $roomService,
        private BookingNumberService $bookingNumberService,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function store(CreateBookingData $data, int $userId): Booking
    {
        $hotel = Hotel::published()->findOrFail($data->hotelId);
        $hotel->load('rooms');

        $startDate = Carbon::parse($data->checkIn)->startOfDay();
        $endDate = Carbon::parse($data->checkOut)->endOfDay();
        $adults = $data->adults;
        $children = 0;
        $hunters = $data->hunters;
        $animalId = $data->animalId;
        $totalGuests = $adults + $children;
        $numberDays = $startDate->diffInDays($endDate);

        $selectedRooms = [];
        $total = 0.0;

        if (!empty($data->rooms)) {
            $availableRooms = collect($this->roomService->getAvailableRooms($hotel, [
                'check_in' => $data->checkIn,
                'check_out' => $data->checkOut,
                'adults' => $adults,
            ]))->keyBy('id');

            if ($availableRooms->isEmpty()) {
                throw new ValidationException(
                    message: __('booking.errors.no_rooms_available'),
                    errorCode: 'no_rooms_available',
                    domain: 'booking'
                );
            }

            $totalAdultCapacity = 0;

            foreach ($data->rooms as $roomInput) {
                /** @var HotelRoom|null $room */
                $room = $availableRooms->get($roomInput->roomId);

                if (!$room || $roomInput->number > (int) ($room->available_number ?? $room->tmp_number ?? 0)) {
                    throw new ValidationException(
                        message: __('booking.errors.room_not_available'),
                        errorCode: 'room_not_available',
                        domain: 'booking'
                    );
                }

                $minDayStays = (int) ($room->min_day_stays ?? 0);
                if ($minDayStays > 0 && $numberDays < $minDayStays) {
                    throw new ValidationException(
                        message: __('booking.errors.min_day_stays', [
                            'name' => $room->title,
                            'number' => $minDayStays,
                        ]),
                        errorCode: 'min_day_stays',
                        domain: 'booking'
                    );
                }

                $roomPrice = (float) ($room->calculated_price ?? $room->tmp_price ?? 0);
                $total += $roomPrice * $roomInput->number;
                $totalAdultCapacity += (int) ($room->adults ?? 0) * $roomInput->number;

                $selectedRooms[] = [
                    'room' => $room,
                    'number' => $roomInput->number,
                    'price' => $roomPrice,
                ];
            }

            if ($adults > $totalAdultCapacity) {
                throw new ValidationException(
                    message: __('booking.errors.not_enough_adult_capacity'),
                    errorCode: 'not_enough_adult_capacity',
                    domain: 'booking'
                );
            }
        } elseif (!$animalId) {
            throw new ValidationException(
                message: __('booking.validation.rooms_or_animal_required'),
                errorCode: 'rooms_or_animal_required',
                domain: 'booking'
            );
        }

        $type = $animalId
            ? (!empty($data->rooms) ? 'hotel_animal' : 'animal')
            : 'hotel';
        $amountHunting = 0.0;
        $startDateAnimal = null;

        if ($animalId) {
            $period = AnimalPricePeriod::query()
                ->where('animal_id', $animalId)
                ->whereDate('start_date', '<=', $data->checkIn)
                ->whereDate('end_date', '>=', $data->checkIn)
                ->first();

            if (!$period) {
                throw new ValidationException(
                    message: __('booking.errors.animal_price_not_found'),
                    errorCode: 'animal_price_not_found',
                    domain: 'booking'
                );
            }

            $amountHunting = $hunters * (float) $period->price;
            $startDateAnimal = $startDate->copy();
        }

        return DB::transaction(function () use (
            $hotel,
            $userId,
            $startDate,
            $endDate,
            $adults,
            $children,
            $hunters,
            $animalId,
            $totalGuests,
            $total,
            $amountHunting,
            $startDateAnimal,
            $type,
            $selectedRooms,
            $numberDays
        ) {
            $totalBeforeFees = $total;

            $booking = new Booking();
            $booking->status = Booking::PROCESSING;
            $booking->object_id = $hotel->id;
            $booking->object_model = 'hotel';
            $booking->vendor_id = $hotel->author_id ?? null;
            $booking->customer_id = $userId;
            $booking->create_user = $userId;
            $booking->total = $total;
            $booking->amount_hunting = $amountHunting;
            $booking->total_guests = $totalGuests;
            $booking->total_hunting = $animalId ? $hunters : null;
            $booking->start_date = $startDate;
            $booking->end_date = $endDate;
            $booking->hotel_id = $hotel->id;
            $booking->animal_id = $animalId;
            $booking->start_date_animal = $startDateAnimal;
            $booking->type = $type;
            $booking->total_before_fees = $totalBeforeFees;
            $booking->total_before_discount = $totalBeforeFees;

            if (is_baseAdmin()) {
                $booking->event = true;
            }

            $booking->calculateCommission();
            $this->applyDeposit($hotel, $booking, $totalBeforeFees);

            if (empty($booking->booking_number)) {
                $booking->booking_number = $this->bookingNumberService->generate($hotel->id);
            }

            $booking->save();

            Booking::clearDraftBookings();

            $booking->addMeta('duration', $numberDays);
            $booking->addMeta('base_price', $hotel->price ?? 0);
            $booking->addMeta('sale_price', $hotel->sale_price ?? 0);
            $booking->addMeta('guests', $totalGuests);
            $booking->addMeta('adults', $adults);
            $booking->addMeta('children', $children);

            if ($hotel->isDepositEnable()) {
                $booking->addMeta('deposit_info', [
                    'type' => $hotel->getDepositType(),
                    'amount' => $hotel->getDepositAmount(),
                    'fomular' => $hotel->getDepositFomular(),
                ]);
            }

            foreach ($selectedRooms as $selected) {
                /** @var HotelRoom $room */
                $room = $selected['room'];

                $hotelRoomBooking = HotelRoomBooking::create([
                    'room_id' => $room->id,
                    'parent_id' => $hotel->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'number' => $selected['number'],
                    'booking_id' => $booking->id,
                    'price' => $selected['price'],
                ]);

                $dayStart = Carbon::parse($hotelRoomBooking->start_date)->startOfDay();
                $dayEnd = Carbon::parse($hotelRoomBooking->end_date)->startOfDay();

                for ($date = $dayStart->copy(); $date->lt($dayEnd); $date->addDay()) {
                    BookedDay::create([
                        'booking_id' => $hotelRoomBooking->id,
                        'room_id' => $hotelRoomBooking->room_id,
                        'date' => $date->format('Y-m-d'),
                        'number' => $hotelRoomBooking->number,
                    ]);
                }
            }

            return $booking->fresh();
        });
    }

    private function applyDeposit(Hotel $hotel, Booking $booking, float $totalBeforeFees): void
    {
        if (!$hotel->isDepositEnable()) {
            return;
        }

        $bookingDepositFormula = $hotel->getDepositFomular();
        $tmpPriceTotal = $booking->total;

        if ($bookingDepositFormula === 'deposit_and_fee') {
            $tmpPriceTotal = $totalBeforeFees;
        }

        $booking->deposit = match ($hotel->getDepositType()) {
            'percent' => $tmpPriceTotal * $hotel->getDepositAmount() / 100,
            default => $hotel->getDepositAmount(),
        };

        if ($bookingDepositFormula === 'deposit_and_fee') {
            $booking->deposit = ($booking->deposit ?? 0);
        }
    }
}
