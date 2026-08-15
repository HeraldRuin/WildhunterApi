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
use Modules\Booking\Dto\BookingHistoryData;
use Modules\Booking\Dto\CreateBookingData;
use Modules\Booking\Dto\ReplaceHunterData;
use Modules\Booking\Dto\SelectPlaceData;
use Modules\Booking\Dto\StoreAdditionalData;
use Modules\Booking\Dto\StoreFoodData;
use Modules\Booking\Dto\StorePenaltyData;
use Modules\Booking\Dto\StorePreparationData;
use Modules\Booking\Dto\StoreSpendingData;
use Modules\Booking\Dto\StoreTrophyData;
use Modules\Booking\Dto\UpdateCustomerNotesData;
use Modules\Booking\Http\Requests\BookingCreateRequest;
use Modules\Booking\Http\Requests\BookingHistoryRequest;
use Modules\Booking\Http\Requests\CancelSelectPlaceRequest;
use Modules\Booking\Http\Requests\ChangeBookingCustomerRequest;
use Modules\Booking\Http\Requests\InviteHunterRequest;
use Modules\Booking\Http\Requests\RemoveHunterRequest;
use Modules\Booking\Http\Requests\ReplaceHunterRequest;
use Modules\Booking\Http\Requests\SelectPlaceRequest;
use Modules\Booking\Http\Requests\StoreAdditionalRequest;
use Modules\Booking\Http\Requests\StoreFoodRequest;
use Modules\Booking\Http\Requests\StorePenaltyRequest;
use Modules\Booking\Http\Requests\StorePreparationRequest;
use Modules\Booking\Http\Requests\StoreSpendingRequest;
use Modules\Booking\Http\Requests\StoreTrophyRequest;
use Modules\Booking\Http\Requests\UpdateCustomerNotesRequest;
use Modules\Booking\Http\Resources\BookingCheckoutResource;
use Modules\Booking\Http\Resources\BookingHistoryResource;
use Modules\Booking\Http\Resources\CollectionTimerResource;
use Modules\Booking\Services\BookingCancelService;
use Modules\Booking\Services\BookingCheckoutService;
use Modules\Booking\Services\BookingCollectionService;
use Modules\Booking\Services\BookingConfirmService;
use Modules\Booking\Services\BookingCustomerService;
use Modules\Booking\Services\BookingHistoryService;
use Modules\Booking\Services\BookingInvitationService;
use Modules\Booking\Services\BookingMarkPaidService;
use Modules\Booking\Services\BookingPlaceService;
use Modules\Booking\Services\BookingServiceManager;
use Modules\Booking\Services\BookingStoreService;
use Modules\Booking\Services\PaymentManagerService;

class BookingController extends Controller
{
    public function __construct(
        protected BookingStoreService $bookingStoreService,
        protected BookingCheckoutService $bookingCheckoutService,
        protected BookingHistoryService $bookingHistoryService,
        protected BookingConfirmService $bookingConfirmService,
        protected BookingCustomerService $bookingCustomerService,
        protected BookingCancelService $bookingCancelService,
        protected BookingCollectionService $bookingCollectionService,
        protected BookingInvitationService $bookingInvitationService,
        protected BookingPlaceService $bookingPlaceService,
        protected BookingMarkPaidService $bookingMarkPaidService,
        protected PaymentManagerService $paymentManagerService,
        protected BookingServiceManager $bookingServiceManager,
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
     * Смена заказчика бронирования администратором базы.
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function changeCustomer(ChangeBookingCustomerRequest $request, string $code): JsonResponse
    {
        $result = $this->bookingCustomerService->change(
            $code,
            $request->integer('user_id'),
            Auth::user(),
        );

        return new SuccessResponse(
            code: $result['code'],
            domain: 'booking',
        );
    }

    /**
     * История бронирований (охотник / администратор базы — по роли текущего пользователя).
     */
    public function bookingHistory(BookingHistoryRequest $request): JsonResponse
    {
        $dto = BookingHistoryData::fromRequest($request);
        $result = $this->bookingHistoryService->getHistory(Auth::user(), $dto);

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
     * Отметка брони как оплаченной администратором базы.
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function markPaid(string $code): JsonResponse
    {
        $booking = $this->bookingMarkPaidService->markPaid($code, Auth::user());

        return new SuccessResponse(
            code: 'booking_marked_paid',
            domain: 'booking',
            data: [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
                'is_paid' => (bool) $booking->is_paid,
            ],
        );
    }

    /**
     * Запуск сбора охотников мастер-охотником.
     *
     */
    public function startCollection(string $code): JsonResponse
    {
        $result = $this->bookingCollectionService->start($code, Auth::user());

        return new SuccessResponse(
            code: 'gathering_has_started',
            domain: 'collection',
            data: new CollectionTimerResource($result),
        );
    }

    /**
     * Приглашение охотника в активный сбор.
     *
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function inviteHunter(InviteHunterRequest $request, string $code): JsonResponse
    {
        $invitation = $this->bookingInvitationService->invite(
            $code,
            (int) $request->validated('hunter_id'),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'booking_invitation_sent',
            domain: 'booking',
            data: [
                'invitation_id' => $invitation->id,
                'hunter_id' => $invitation->hunter_id,
                'status' => $invitation->status,
            ],
        );
    }

    /**
     * Удаление охотника с неоплаченной предоплатой после завершения сбора.
     *
     */
    public function removeHunter(RemoveHunterRequest $request, string $code): JsonResponse
    {
        $this->bookingInvitationService->remove(
            $code,
            (int) $request->validated('hunter_id'),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'hunter_removed',
            domain: 'booking',
        );
    }

    /**
     * Замена недоплатившего охотника после завершения сбора.
     *
     */
    public function replaceHunter(ReplaceHunterRequest $request, string $code): JsonResponse
    {
        $data = ReplaceHunterData::fromRequest($request);
        $result = $this->bookingInvitationService->replace(
            $code,
            $data,
            Auth::user(),
        );
        $invitation = $result->invitation;
        $hunter = $result->hunter;

        return new SuccessResponse(
            code: 'hunter_replace',
            domain: 'booking',
            data: [
                'invitation_id' => $invitation->id,
                'hunter_id' => $hunter->id,
                'email' => $hunter->email,
                'first_name' => $hunter->first_name,
                'last_name' => $hunter->last_name,
                'user_name' => $hunter->user_name,
                'invitation_status' => $invitation->status,
                'prepayment_paid' => (bool) $invitation->prepayment_paid,
                'prepayment_paid_status' => $invitation->prepayment_paid_status,
            ],
        );
    }

    /**
     * Принятие приглашения текущим охотником.
     *
     * @throws NotFoundException
     */
    public function acceptInvitation(string $code): JsonResponse
    {
        $invitation = $this->bookingInvitationService->accept($code, Auth::user());

        return new SuccessResponse(
            code: 'invitation_accepted',
            domain: 'booking',
            data: [
                'status' => __('statuses.invitation.' . $invitation->status),
                'accepted_at' => $invitation->accepted_at,
            ],
        );
    }

    /**
     * Отклонение приглашения текущим охотником.
     *
     * @throws NotFoundException
     */
    public function declineInvitation(string $code): JsonResponse
    {
        $invitation = $this->bookingInvitationService->decline($code, Auth::user());

        return new SuccessResponse(
            code: 'invitation_declined',
            domain: 'booking',
            data: [
                'status' => __('statuses.invitation.' . $invitation->status),
                'declined_at' => $invitation->declined_at,
            ],
        );
    }

    /**
     * Продление истёкшего сбора охотников мастер-охотником.
     */
    public function extendCollection(string $code): JsonResponse
    {
        $result = $this->bookingCollectionService->extend($code, Auth::user());

        return new SuccessResponse(
            code: 'gathering_has_extended',
            domain: 'collection',
            data: new CollectionTimerResource($result),
        );
    }

    /**
     * Завершение сбора охотников мастер-охотником.
     *
     */
    public function finishCollection(string $code): JsonResponse
    {
        $result = $this->bookingCollectionService->finish($code, Auth::user());
        $booking = $result['booking'];
        $data = [
            'id' => $booking->id,
            'code' => $booking->code,
            'status' => $booking->status,
        ];

        if (isset($result['start_at'], $result['end_at'], $result['hours'])) {
            $data['paid_start_at'] = $result['start_at'];
            $data['paid_end_at'] = $result['end_at'];
            $data['paid_timer_hours'] = $result['hours'];
        }

        return new SuccessResponse(
            code: 'gathering_has_completed',
            domain: 'booking',
            data: $data,
        );
    }

    /**
     * Фиксация неоплаченных приглашений после окончания таймера предоплаты.
     */
    public function expirePrepaymentCollection(string $code): JsonResponse
    {
        $this->bookingCollectionService->expirePrepayment($code, Auth::user());

        return new SuccessResponse;
    }

    public function storePrepayment(string $code): JsonResponse
    {
        return new SuccessResponse(
            data: $this->paymentManagerService->createPayment($code, Auth::user()),
        );
    }

    public function paymentStatus(string $code): JsonResponse
    {
        return new SuccessResponse(
            data: $this->paymentManagerService->getPaymentStatus($code, Auth::user()),
        );
    }

    /**
     * Отмена активного сбора охотников мастером.
     *
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function cancelCollection(string $code): JsonResponse
    {
        $booking = $this->bookingCollectionService->cancel($code, Auth::user());

        return new SuccessResponse(
            code: 'hunter_gathering_cancelled',
            domain: 'booking',
            data: [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
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

    /**
     * Список типов комнат и занятых койко-мест бронирования.
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function places(string $code): JsonResponse
    {
        $data = $this->bookingPlaceService->getPlaces($code, Auth::user());

        return new SuccessResponse(data: $data);
    }

    /**
     * Выбор койко-места текущим охотником.
     *
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function selectPlace(SelectPlaceRequest $request, string $code): JsonResponse
    {
        $dto = SelectPlaceData::fromRequest($request);
        $result = $this->bookingPlaceService->selectPlace($code, $dto, Auth::user());

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    /**
     * Отмена своего выбранного койко-места.
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function cancelSelectPlace(CancelSelectPlaceRequest $request, string $code): JsonResponse
    {
        $result = $this->bookingPlaceService->cancelSelectPlace(
            $code,
            (int) $request->validated('place_id'),
            Auth::user(),
        );

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    /**
     * Каталог и уже добавленные услуги брони (набор зависит от роли).
     *
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function services(string $code): JsonResponse
    {
        return new SuccessResponse(
            data: $this->bookingServiceManager->getServices($code, Auth::user()),
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function storeTrophy(StoreTrophyRequest $request, string $code): JsonResponse
    {
        $data = $this->bookingServiceManager->createTrophy(
            $code,
            StoreTrophyData::fromRequest($request),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'service_added',
            domain: 'booking',
            data: $data,
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function storePenalty(StorePenaltyRequest $request, string $code): JsonResponse
    {
        $data = $this->bookingServiceManager->createPenalty(
            $code,
            StorePenaltyData::fromRequest($request),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'service_added',
            domain: 'booking',
            data: $data,
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function storePreparation(StorePreparationRequest $request, string $code): JsonResponse
    {
        $data = $this->bookingServiceManager->createOrUpdatePreparation(
            $code,
            StorePreparationData::fromRequest($request),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'service_added',
            domain: 'booking',
            data: $data,
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function storeFood(StoreFoodRequest $request, string $code): JsonResponse
    {
        $data = $this->bookingServiceManager->createFood(
            $code,
            StoreFoodData::fromRequest($request),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'service_added',
            domain: 'booking',
            data: $data,
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function storeAdditional(StoreAdditionalRequest $request, string $code): JsonResponse
    {
        $data = $this->bookingServiceManager->createAdditional(
            $code,
            StoreAdditionalData::fromRequest($request),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'service_added',
            domain: 'booking',
            data: $data,
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function storeSpending(StoreSpendingRequest $request, string $code): JsonResponse
    {
        $data = $this->bookingServiceManager->createSpending(
            $code,
            StoreSpendingData::fromRequest($request),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'service_added',
            domain: 'booking',
            data: $data,
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function deleteService(string $code, int $serviceId): JsonResponse
    {
        $this->bookingServiceManager->deleteService($code, $serviceId, Auth::user());

        return new SuccessResponse(
            code: 'service_deleted',
            domain: 'booking',
        );
    }
}
