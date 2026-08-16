@extends('Email::layout')
@section('content')
    {{-- @var \Modules\Booking\Models\Booking $booking --}}
    {{-- @var string $emailType --}}
    {{-- @var string $recipientName --}}
    @php
        $booking->loadMissing(['hotel.location', 'roomsBooking.room', 'creator', 'animal']);
        $hotel = $booking->hotel;
        $animal = $booking->animal;
        $showHotelDetails = $hotel && in_array($booking->type, [
            \Modules\Booking\Models\Booking::BookingTypeHotel,
            \Modules\Booking\Models\Booking::BookingTypeHotelAnimal,
        ], true);
        $showAnimalDetails = $animal && in_array($booking->type, [
            \Modules\Booking\Models\Booking::BookingTypeAnimal,
            \Modules\Booking\Models\Booking::BookingTypeHotelAnimal,
        ], true);
        $rooms = $showHotelDetails ? $booking->roomsBooking : collect();
        $adults = $booking->getMeta('adults');
        $children = $booking->getMeta('children');
        $paid = (float) ($booking->paid ?? 0);
        $total = (float) ($booking->total ?? 0);
        $siteUrl = rtrim((string) (setting_item('site_url') ?: config('app.url')), '/');
        $hotelUrl = ($hotel?->location?->slug && $hotel?->slug)
            ? $siteUrl.'/hotel/'.$hotel->location->slug.'/'.$hotel->slug
            : null;
        $bookingsUrl = $siteUrl.'/profile/bookings';
        $address = $hotel?->address ?: $hotel?->location?->name;
        $creator = $booking->creator;
        $emailType = $emailType ?? 'admin';
        if (!isset($recipientName) || $recipientName === '') {
            $recipientUser = $emailType === 'customer'
                ? $creator
                : ($baseAdmin ?? null);
            $recipientName = trim(($recipientUser->first_name ?? '') . ' ' . ($recipientUser->last_name ?? ''));
            if ($recipientName === '') {
                $recipientName = (string) ($recipientUser->user_name ?? $recipientUser->email ?? (
                    $emailType === 'customer'
                        ? __('booking.email.hunter')
                        : __('booking.email.base_admin')
                ));
            }
        }
    @endphp
    <div class="b-container">
        <div class="b-panel">
            <h3 class="email-headline"><strong>{{ __('booking.email.hello', ['name' => $recipientName]) }}</strong></h3>
            @if(!empty($isStatusUpdate))
                @if($emailType === 'admin')
                    <p>{{ __('booking.email.status_updated_admin_body') }}</p>
                @else
                    <p>{{ __('booking.email.status_updated_customer_body') }}</p>
                @endif
            @elseif($emailType === 'admin')
                <p>{{ __('booking.email.new_booking_admin_body') }}</p>
            @else
                <p>{{ __('booking.email.new_booking_customer_body') }}</p>
            @endif

            <div class="b-table-wrap mb-4">
                <table class="b-table" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="label">{{ __('booking.email.booking_number') }}</td>
                        <td class="val">#{{ $booking->booking_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('booking.email.booking_status') }}</td>
                        <td class="val">{{ $booking->statusName }}</td>
                    </tr>
                </table>
            </div>

            @include('Booking::emails.parts.booking-details')

            @if($emailType === 'customer' && ($showHotelDetails || $showAnimalDetails))
                <div class="text-center mt20">
                    <a href="{{ $bookingsUrl }}" target="_blank" class="btn btn-primary">{{ __('booking.email.manage_bookings') }}</a>
                </div>
            @endif
        </div>

        @if($emailType === 'admin')
            @include('Booking::emails.parts.panel-customer')
        @endif
    </div>
@endsection
