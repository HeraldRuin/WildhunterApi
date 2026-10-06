<?php

namespace Tests\Feature\Booking;

use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Services\BookingHistoryActionService;
use Modules\Role\Models\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingHistoryActionServiceTest extends TestCase
{
    private BookingHistoryActionService $actions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actions = new BookingHistoryActionService();
    }

    #[DataProvider('adminLodgingActions')]
    public function test_base_admin_lodging_actions(string $status, array $expected): void
    {
        foreach ([Booking::BookingTypeHotel, Booking::BookingTypeHotelAnimal] as $type) {
            $this->assertCodes($this->booking($status, $type), Role::ADMIN, $expected);
        }
    }

    #[DataProvider('adminAnimalActions')]
    public function test_base_admin_animal_actions(string $status, array $expected): void
    {
        $this->assertCodes(
            $this->booking($status, Booking::BookingTypeAnimal),
            Role::ADMIN,
            $expected,
        );
    }

    #[DataProvider('masterLodgingActions')]
    public function test_master_hunter_lodging_actions(string $status, array $expected): void
    {
        foreach ([Booking::BookingTypeHotel, Booking::BookingTypeHotelAnimal] as $type) {
            $this->assertCodes(
                $this->booking($status, $type, master: true),
                Role::CUSTOMER,
                $expected,
            );
        }
    }

    #[DataProvider('masterAnimalActions')]
    public function test_master_hunter_animal_actions(string $status, array $expected): void
    {
        $this->assertCodes(
            $this->booking($status, Booking::BookingTypeAnimal, master: true),
            Role::CUSTOMER,
            $expected,
        );
    }

    #[DataProvider('invitedPendingActions')]
    public function test_invited_hunter_without_accepted_invitation(string $status, string $type, array $expected): void
    {
        $this->assertCodes(
            $this->booking($status, $type, invited: true, invitationStatus: BookingHunterInvitation::STATUS_PENDING),
            Role::CUSTOMER,
            $expected,
        );
    }

    #[DataProvider('invitedLodgingActions')]
    public function test_accepted_invited_hunter_lodging_actions(string $status, array $expected): void
    {
        foreach ([Booking::BookingTypeHotel, Booking::BookingTypeHotelAnimal] as $type) {
            $this->assertCodes(
                $this->booking(
                    $status,
                    $type,
                    invited: true,
                    invitationStatus: BookingHunterInvitation::STATUS_ACCEPTED,
                ),
                Role::CUSTOMER,
                $expected,
            );
        }
    }

    #[DataProvider('invitedAnimalActions')]
    public function test_accepted_invited_hunter_animal_actions(string $status, array $expected): void
    {
        $this->assertCodes(
            $this->booking(
                $status,
                Booking::BookingTypeAnimal,
                invited: true,
                invitationStatus: BookingHunterInvitation::STATUS_ACCEPTED,
            ),
            Role::CUSTOMER,
            $expected,
        );
    }

    public function test_action_labels_come_from_booking_translations(): void
    {
        $actions = $this->actions->getAvailableActions(
            $this->booking(Booking::PROCESSING, Booking::BookingTypeHotel),
            Role::ADMIN,
        );

        $this->assertSame([
            ['code' => 'confirm', 'label' => 'Подтвердить бронь'],
            ['code' => 'cancel', 'label' => 'Отменить бронь'],
        ], $actions);
    }

    public function test_hunter_who_is_neither_master_nor_invited_has_no_actions(): void
    {
        $this->assertCodes(
            $this->booking(Booking::CONFIRMED, Booking::BookingTypeHotel),
            Role::CUSTOMER,
            [],
        );
    }

    public function test_invited_master_without_accepted_invitation_only_opens_invitation(): void
    {
        $this->assertCodes(
            $this->booking(
                Booking::PREPAYMENT_COLLECTION,
                Booking::BookingTypeHotel,
                invited: true,
                master: true,
                invitationStatus: BookingHunterInvitation::STATUS_PENDING,
            ),
            Role::CUSTOMER,
            ['open_invitation'],
        );
    }

    public function test_accepted_invited_master_has_no_actions(): void
    {
        $this->assertCodes(
            $this->booking(
                Booking::PREPAYMENT_COLLECTION,
                Booking::BookingTypeHotel,
                invited: true,
                master: true,
                invitationStatus: BookingHunterInvitation::STATUS_ACCEPTED,
            ),
            Role::CUSTOMER,
            [],
        );
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function adminLodgingActions(): array
    {
        return [
            'processing' => [Booking::PROCESSING, ['confirm', 'cancel']],
            'confirmed' => [Booking::CONFIRMED, ['cancel']],
            'collection' => [Booking::START_COLLECTION, ['cancel']],
            'prepayment_collection' => [Booking::PREPAYMENT_COLLECTION, ['cancel', 'add_services', 'calculating']],
            'finish_prepayment' => [Booking::FINISHED_PREPAYMENT, ['cancel', 'add_services', 'calculating']],
            'bed_collection' => [Booking::BED_COLLECTION, ['cancel', 'add_services', 'calculating']],
            'finish_bed_collection' => [Booking::FINISHED_BED, ['cancel', 'add_services', 'calculating', 'mark_paid']],
            'paid' => [Booking::PAID, ['complete', 'calculating']],
            'completed' => [Booking::COMPLETED, ['calculating']],
            'cancelled' => [Booking::CANCELLED, []],
            'finished_collection' => [Booking::FINISHED_COLLECTION, []],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function adminAnimalActions(): array
    {
        return [
            'processing' => [Booking::PROCESSING, ['confirm', 'cancel']],
            'confirmed' => [Booking::CONFIRMED, ['cancel']],
            'collection' => [Booking::START_COLLECTION, ['cancel']],
            'finished_collection' => [Booking::FINISHED_COLLECTION, ['cancel', 'add_services', 'mark_paid', 'calculating']],
            'paid' => [Booking::PAID, ['complete', 'calculating']],
            'completed' => [Booking::COMPLETED, ['calculating']],
            'cancelled' => [Booking::CANCELLED, []],
            'prepayment_collection' => [Booking::PREPAYMENT_COLLECTION, []],
            'finish_prepayment' => [Booking::FINISHED_PREPAYMENT, []],
            'bed_collection' => [Booking::BED_COLLECTION, []],
            'finish_bed_collection' => [Booking::FINISHED_BED, []],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function masterLodgingActions(): array
    {
        return [
            'processing' => [Booking::PROCESSING, ['cancel']],
            'confirmed' => [Booking::CONFIRMED, ['cancel', 'start_collection']],
            'collection' => [Booking::START_COLLECTION, ['cancel', 'open_collection', 'cancel_collection']],
            'prepayment_collection' => [Booking::PREPAYMENT_COLLECTION, ['cancel', 'open_collection', 'prepayment']],
            'finish_prepayment' => [Booking::FINISHED_PREPAYMENT, ['cancel', 'open_collection', 'select_place', 'add_services', 'calculating']],
            'bed_collection' => [Booking::BED_COLLECTION, ['cancel', 'open_collection', 'select_place', 'add_services', 'calculating']],
            'finish_bed_collection' => [Booking::FINISHED_BED, ['cancel', 'open_collection', 'select_place', 'add_services', 'calculating']],
            'paid' => [Booking::PAID, ['calculating']],
            'completed' => [Booking::COMPLETED, ['calculating']],
            'cancelled' => [Booking::CANCELLED, []],
            'finished_collection' => [Booking::FINISHED_COLLECTION, []],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function masterAnimalActions(): array
    {
        return [
            'processing' => [Booking::PROCESSING, ['cancel']],
            'confirmed' => [Booking::CONFIRMED, ['cancel', 'start_collection']],
            'collection' => [Booking::START_COLLECTION, ['cancel', 'open_collection', 'cancel_collection']],
            'finished_collection' => [Booking::FINISHED_COLLECTION, ['cancel', 'open_collection', 'add_services', 'calculating']],
            'paid' => [Booking::PAID, ['calculating']],
            'completed' => [Booking::COMPLETED, ['calculating']],
            'cancelled' => [Booking::CANCELLED, []],
            'prepayment_collection' => [Booking::PREPAYMENT_COLLECTION, []],
            'bed_collection' => [Booking::BED_COLLECTION, []],
            'finish_bed_collection' => [Booking::FINISHED_BED, []],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: list<string>}>
     */
    public static function invitedPendingActions(): array
    {
        $open = ['open_invitation'];
        $cases = [];

        foreach ([Booking::BookingTypeHotel, Booking::BookingTypeAnimal] as $type) {
            foreach ([
                Booking::START_COLLECTION,
                Booking::PREPAYMENT_COLLECTION,
                Booking::FINISHED_PREPAYMENT,
                Booking::BED_COLLECTION,
                Booking::FINISHED_BED,
                Booking::PAID,
                Booking::COMPLETED,
            ] as $status) {
                $cases[$type.' '.$status] = [$status, $type, $open];
            }

            foreach ([
                Booking::PROCESSING,
                Booking::CONFIRMED,
                Booking::FINISHED_COLLECTION,
                Booking::CANCELLED,
            ] as $status) {
                $cases[$type.' '.$status] = [$status, $type, []];
            }
        }

        return $cases;
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function invitedLodgingActions(): array
    {
        return [
            'prepayment_collection' => [Booking::PREPAYMENT_COLLECTION, ['open_collection', 'prepayment']],
            'finish_prepayment' => [Booking::FINISHED_PREPAYMENT, ['open_collection', 'select_place', 'calculating']],
            'collection' => [Booking::START_COLLECTION, ['open_collection']],
            'bed_collection' => [Booking::BED_COLLECTION, ['open_collection', 'select_place', 'calculating']],
            'finish_bed_collection' => [Booking::FINISHED_BED, ['open_collection', 'select_place', 'calculating']],
            'paid' => [Booking::PAID, ['calculating']],
            'completed' => [Booking::COMPLETED, ['calculating']],
            'processing' => [Booking::PROCESSING, []],
            'confirmed' => [Booking::CONFIRMED, []],
            'finished_collection' => [Booking::FINISHED_COLLECTION, []],
            'cancelled' => [Booking::CANCELLED, []],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function invitedAnimalActions(): array
    {
        return [
            'prepayment_collection' => [Booking::PREPAYMENT_COLLECTION, ['open_collection']],
            'finish_prepayment' => [Booking::FINISHED_PREPAYMENT, ['open_collection']],
            'collection' => [Booking::START_COLLECTION, ['open_collection']],
            'bed_collection' => [Booking::BED_COLLECTION, ['open_collection']],
            'finish_bed_collection' => [Booking::FINISHED_BED, ['open_collection']],
            'finished_collection' => [Booking::FINISHED_COLLECTION, ['calculating']],
            'paid' => [Booking::PAID, ['calculating']],
            'completed' => [Booking::COMPLETED, ['calculating']],
            'processing' => [Booking::PROCESSING, []],
            'confirmed' => [Booking::CONFIRMED, []],
            'cancelled' => [Booking::CANCELLED, []],
        ];
    }

    private function booking(
        string $status,
        string $type,
        bool $invited = false,
        bool $master = false,
        ?string $invitationStatus = null,
    ): Booking {
        $booking = new class extends Booking {
            public bool $testInvited = false;

            public bool $testMaster = false;

            public ?BookingHunterInvitation $testInvitation = null;

            public function getIsInvitedAttribute(): bool
            {
                return $this->testInvited;
            }

            public function getIsMasterHunterAttribute(): bool
            {
                return $this->testMaster;
            }

            public function getCurrentUserInvitation(): ?BookingHunterInvitation
            {
                return $this->testInvitation;
            }
        };

        $booking->status = $status;
        $booking->type = $type;
        $booking->testInvited = $invited;
        $booking->testMaster = $master;

        if ($invitationStatus !== null) {
            $invitation = new BookingHunterInvitation();
            $invitation->status = $invitationStatus;
            $booking->testInvitation = $invitation;
        }

        return $booking;
    }

    /**
     * @param list<string> $expected
     */
    private function assertCodes(Booking $booking, string $role, array $expected): void
    {
        $actions = $this->actions->getAvailableActions($booking, $role);

        $this->assertSame(
            $expected,
            array_column($actions, 'code'),
            $role.' / '.$booking->type.' / '.$booking->status,
        );

        foreach ($actions as $action) {
            $this->assertNotSame('', $action['label']);
        }
    }
}
