<?php

namespace Modules\Booking\Controllers;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginateResource;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Dto\CreateBookingData;
use Modules\Booking\Dto\UpdateCustomerNotesData;
use Modules\Booking\Http\Requests\BookingCreateRequest;
use Modules\Booking\Http\Requests\BookingHistoryRequest;
use Modules\Booking\Http\Requests\UpdateCustomerNotesRequest;
use Modules\Booking\Http\Resources\BookingCheckoutResource;
use Modules\Booking\Http\Resources\BookingHistoryResource;
use Modules\Booking\Services\BookingCancelService;
use Modules\Booking\Services\BookingCheckoutService;
use Modules\Booking\Services\BookingConfirmService;
use Modules\Booking\Services\BookingHistoryService;
use Modules\Booking\Services\BookingStartCollectionService;
use Modules\Booking\Services\BookingStoreService;

class BookingController extends Controller
{
    public function __construct(
        protected BookingStoreService $bookingStoreService,
        protected BookingCheckoutService $bookingCheckoutService,
        protected BookingHistoryService $bookingHistoryService,
        protected BookingConfirmService $bookingConfirmService,
        protected BookingCancelService $bookingCancelService,
        protected BookingStartCollectionService $bookingStartCollectionService,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function store(BookingCreateRequest $request): JsonResponse
    {
        $dto = CreateBookingData::fromRequest($request);
        $booking = $this->bookingStoreService->store($dto, Auth::id());

        return new SuccessResponse(
            code: 'booking_created',
            domain: 'booking',
            status: 201,
            data: [
                'booking_code' => $booking->code,
            ],
        );
    }

    /**
     * @throws ForbiddenException
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function checkout(string $code): JsonResponse
    {
        $booking = $this->bookingCheckoutService->findForCheckout($code, Auth::id());

        return new SuccessResponse(data: new BookingCheckoutResource($booking));
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updateCustomerNotes(UpdateCustomerNotesRequest $request): JsonResponse
    {
        $dto = UpdateCustomerNotesData::fromRequest($request);
        $booking = $this->bookingCheckoutService->updateCustomerNotes($dto, Auth::id());

        return new SuccessResponse(
            code: 'customer_notes_updated',
            domain: 'booking',
            data: [
                'customer_notes' => $booking->customer_notes,
            ],
        );
    }

    /**
     * История бронирований (охотник / администратор базы — по роли текущего пользователя).
     */
    public function bookingHistory(BookingHistoryRequest $request): JsonResponse
    {
        $result = $this->bookingHistoryService->getHistory(Auth::user(), $request->input('status'), $request->integer('booking_id') ?: null);

        return new SuccessResponse(data: [
            'role' => $result['role'],
            'hotel' => $result['hotel'],
            'statuses' => $result['statuses'],
            'dropdown_statuses' => $result['dropdown_statuses'],
            'bookings' => (new PaginateResource($result['bookings'], BookingHistoryResource::class))->resolve(),
        ]);
    }

    /**
     * Подтверждение брони администратором базы.
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function confirm(string $code): JsonResponse
    {
        $booking = $this->bookingConfirmService->confirm($code, Auth::user());

        return new SuccessResponse(
            code: 'booking_confirmed',
            domain: 'booking',
            data: [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
            ],
        );
    }

    /**
     * Запуск сбора охотников мастер-охотником.
     *
     */
    public function startCollection(string $code): JsonResponse
    {
        $result = $this->bookingStartCollectionService->start($code, Auth::user());
        $booking = $result['booking'];

        return new SuccessResponse(
            code: 'gathering_has_started',
            domain: 'collection',
            data: [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
                'collection_start_at' => $result['start_at'],
                'collection_end_at' => $result['end_at'],
                'collection_timer_hours' => $result['hours'],
            ],
        );
    }

    /**
     * Отмена брони (администратор базы / заказчик).
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function cancel(string $code): JsonResponse
    {
        $booking = $this->bookingCancelService->cancel($code, Auth::user());

        return new SuccessResponse(
            code: 'booking_cancelled',
            domain: 'booking',
            data: [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
            ],
        );
    }
}
