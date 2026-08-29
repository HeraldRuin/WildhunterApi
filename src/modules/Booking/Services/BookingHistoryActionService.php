<?php

namespace Modules\Booking\Services;

use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Role\Models\Role;

class BookingHistoryActionService
{
    /**
     * @return list<array{code: string, label: string}>
     */
    public function getAvailableActions(Booking $booking, string $role): array
    {
        $codes = $role === Role::ADMIN
            ? $this->forBaseAdmin($booking)
            : $this->forHunter($booking);

        return array_map(static fn (string $code) => [
            'code' => $code,
            'label' => __('booking.actions.' . $code),
        ], $codes);
    }

    /**
     * @return list<string>
     */
    private function forBaseAdmin(Booking $booking): array
    {
        $status = $booking->status;
        $actions = [];

        if ($status === Booking::PROCESSING) {
            $actions[] = 'confirm';
        }

        if ($status === Booking::PAID) {
            $actions[] = 'complete';
        }

        if ($booking->type === Booking::BookingTypeAnimal) {
            if (in_array($status, [
                Booking::PROCESSING,
                Booking::CONFIRMED,
                Booking::START_COLLECTION,
                Booking::FINISHED_COLLECTION,
            ], true)) {
                $actions[] = 'cancel';
            }

            if ($status === Booking::FINISHED_COLLECTION) {
                $actions[] = 'add_services';
                $actions[] = 'mark_paid';
            }

            if (in_array($status, [
                Booking::FINISHED_COLLECTION,
                Booking::PAID,
                Booking::COMPLETED,
            ], true)) {
                $actions[] = 'calculating';
            }

            return $actions;
        }

        // hotel / hotel_animal
        if (in_array($status, [
            Booking::PROCESSING,
            Booking::CONFIRMED,
            Booking::PREPAYMENT_COLLECTION,
            Booking::START_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], true)) {
            $actions[] = 'cancel';
        }

        if (in_array($status, [
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], true)) {
            $actions[] = 'add_services';
        }

        if (in_array($status, [
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
            Booking::PAID,
            Booking::COMPLETED,
        ], true)) {
            $actions[] = 'calculating';
        }

        if ($status === Booking::FINISHED_BED) {
            $actions[] = 'mark_paid';
        }

        return $actions;
    }

    /**
     * @return list<string>
     */
    private function forHunter(Booking $booking): array
    {
        $isInvited = (bool) $booking->is_invited;
        $isMaster = (bool) $booking->is_master_hunter;
        $invitation = $booking->getCurrentUserInvitation();
        $isInvitationAccepted = $invitation
            && $invitation->status === BookingHunterInvitation::STATUS_ACCEPTED;

        if ($isInvited) {
            return $this->forInvitedHunter($booking, $isMaster, $isInvitationAccepted);
        }

        if (!$isMaster) {
            return [];
        }

        return $booking->type === Booking::BookingTypeAnimal
            ? $this->forMasterHunterAnimal($booking)
            : $this->forMasterHunterHotel($booking);
    }

    /**
     * @return list<string>
     */
    private function forInvitedHunter(Booking $booking, bool $isMaster, bool $isInvitationAccepted): array
    {
        $actions = [];
        $status = $booking->status;

        $isCollectionStatus = in_array($status, [
            Booking::START_COLLECTION,
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
            Booking::PAID,
            Booking::COMPLETED,
        ], true);

        if (!$isInvitationAccepted) {
            if ($isCollectionStatus) {
                $actions[] = 'open_invitation';
            }

            return $actions;
        }

        if ($isMaster) {
            return [];
        }

        if ($booking->type === Booking::BookingTypeAnimal) {
            if (in_array($status, [
                Booking::PREPAYMENT_COLLECTION,
                Booking::FINISHED_PREPAYMENT,
                Booking::START_COLLECTION,
                Booking::BED_COLLECTION,
                Booking::FINISHED_BED,
            ], true)) {
                $actions[] = 'open_collection';
            }

            if (in_array($status, [
                Booking::FINISHED_COLLECTION,
                Booking::PAID,
                Booking::COMPLETED,
            ], true)) {
                $actions[] = 'calculating';
            }

            return $actions;
        }

        // hotel / hotel_animal
        if (in_array($status, [
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::START_COLLECTION,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], true)) {
            $actions[] = 'open_collection';
        }

        if (in_array($status, [
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], true)) {
            $actions[] = 'select_place';
        }

        if ($status === Booking::PREPAYMENT_COLLECTION) {
            $actions[] = 'prepayment';
        }

        if (in_array($status, [
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
            Booking::PAID,
            Booking::COMPLETED,
        ], true)) {
            $actions[] = 'calculating';
        }

        return $actions;
    }

    /**
     * @return list<string>
     */
    private function forMasterHunterHotel(Booking $booking): array
    {
        $actions = [];
        $status = $booking->status;

        if (in_array($status, [
            Booking::PROCESSING,
            Booking::CONFIRMED,
            Booking::START_COLLECTION,
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], true)) {
            $actions[] = 'cancel';
        }

        if (in_array($status, [
            Booking::PREPAYMENT_COLLECTION,
            Booking::START_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], true)) {
            $actions[] = 'open_collection';
        } elseif ($status === Booking::CONFIRMED) {
            $actions[] = 'start_collection';
        }

        if ($status === Booking::START_COLLECTION) {
            $actions[] = 'cancel_collection';
        }

        if ($status === Booking::PREPAYMENT_COLLECTION) {
            $actions[] = 'prepayment';
        }

        if (in_array($status, [
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], true)) {
            $actions[] = 'select_place';
            $actions[] = 'add_services';
        }

        if (in_array($status, [
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
            Booking::PAID,
            Booking::COMPLETED,
        ], true)) {
            $actions[] = 'calculating';
        }

        return $actions;
    }

    /**
     * @return list<string>
     */
    private function forMasterHunterAnimal(Booking $booking): array
    {
        $actions = [];
        $status = $booking->status;

        if (in_array($status, [
            Booking::PROCESSING,
            Booking::CONFIRMED,
            Booking::START_COLLECTION,
            Booking::FINISHED_COLLECTION,
        ], true)) {
            $actions[] = 'cancel';
        }

        if (in_array($status, [
            Booking::START_COLLECTION,
            Booking::FINISHED_COLLECTION,
        ], true)) {
            $actions[] = 'open_collection';
        } elseif ($status === Booking::CONFIRMED) {
            $actions[] = 'start_collection';
        }

        if ($status === Booking::START_COLLECTION) {
            $actions[] = 'cancel_collection';
        }

        if ($status === Booking::FINISHED_COLLECTION) {
            $actions[] = 'add_services';
        }

        if (in_array($status, [
            Booking::FINISHED_COLLECTION,
            Booking::PAID,
            Booking::COMPLETED,
        ], true)) {
            $actions[] = 'calculating';
        }

        return $actions;
    }
}
