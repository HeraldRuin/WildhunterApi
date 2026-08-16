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
        $customerFirstName = $booking->first_name ?: $creator?->first_name;
        $customerLastName = $booking->last_name ?: $creator?->last_name;
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
            @if($emailType === 'admin')
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

            @if($showHotelDetails)
                <div class="b-panel-title">{{ __('booking.email.hotel_details') }}</div>
                <div class="b-table-wrap">
                    <table class="b-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="label">{{ __('booking.email.payment_method') }}</td>
                            <td class="val">{{ __('booking.email.payment_method_value') }}</td>
                        </tr>
                        <tr>
                            <td class="label">{{ __('booking.email.hotel_name') }}</td>
                            <td class="val">
                                @if($hotelUrl)
                                    <a href="{{ $hotelUrl }}">{{ $hotel->title }}</a>
                                @else
                                    {{ $hotel->title }}
                                @endif
                            </td>
                        </tr>
                        @if($address)
                            <tr>
                                <td class="label">{{ __('booking.email.address') }}</td>
                                <td class="val">{{ $address }}</td>
                            </tr>
                        @endif
                        @if($booking->start_date && $booking->end_date)
                            <tr>
                                <td class="label">{{ __('booking.email.check_in') }}</td>
                                <td class="val">{{ display_date($booking->start_date) }}</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('booking.email.check_out') }}</td>
                                <td class="val">{{ display_date($booking->end_date) }}</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('booking.email.nights') }}</td>
                                <td class="val">{{ $booking->duration_nights }}</td>
                            </tr>
                        @endif
                        @if($adults !== '' && $adults !== null)
                            <tr>
                                <td class="label">{{ __('booking.email.adults') }}</td>
                                <td class="val"><strong>{{ $adults }}</strong></td>
                            </tr>
                        @endif
                        @if($children)
                            <tr>
                                <td class="label">{{ __('booking.email.children') }}</td>
                                <td class="val"><strong>{{ $children }}</strong></td>
                            </tr>
                        @endif
                        @if($rooms->isNotEmpty())
                            <tr>
                                <td class="label">{{ __('booking.email.room_category') }}</td>
                                <td class="val">
                                    <table class="pricing-list" width="100%">
                                        @foreach($rooms as $room)
                                            <tr>
                                                <td class="label">{{ $room->room?->title }} * {{ $room->number }} :</td>
                                                <td class="val no-r-padding">
                                                    <strong>{{ format_money($room->price * $room->number) }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label fsz21">{{ __('booking.email.total') }}</td>
                            <td class="val fsz21"><strong style="color: #FA5636">{{ format_money($total) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label fsz21">{{ __('booking.email.paid') }}</td>
                            <td class="val fsz21"><strong style="color: #FA5636">{{ format_money($paid) }}</strong></td>
                        </tr>
                        @if($total > $paid)
                            <tr>
                                <td class="label fsz21">{{ __('booking.email.remain') }}</td>
                                <td class="val fsz21"><strong style="color: #FA5636">{{ format_money($total - $paid) }}</strong></td>
                            </tr>
                        @endif
                    </table>
                </div>

                @if($emailType === 'customer' && !$showAnimalDetails)
                    <div class="text-center mt20">
                        <a href="{{ $bookingsUrl }}" target="_blank" class="btn btn-primary">{{ __('booking.email.manage_bookings') }}</a>
                    </div>
                @endif
            @endif

            @if($showAnimalDetails)
                <div class="b-panel-title">{{ __('booking.email.animal_details') }}</div>
                <div class="b-table-wrap">
                    <table class="b-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="label">{{ __('booking.email.animal') }}</td>
                            <td class="val">{{ $animal->title }}</td>
                        </tr>
                        @if($booking->total_hunting)
                            <tr>
                                <td class="label">{{ __('booking.email.hunters_count') }}</td>
                                <td class="val"><strong>{{ $booking->total_hunting }}</strong></td>
                            </tr>
                        @endif
                        @if($booking->start_date_animal)
                            <tr>
                                <td class="label">{{ __('booking.email.hunting_date') }}</td>
                                <td class="val">{{ display_date($booking->start_date_animal) }}</td>
                            </tr>
                        @endif
                        @if($booking->amount_hunting)
                            <tr>
                                <td class="label">{{ __('booking.email.hunting_amount') }}</td>
                                <td class="val"><strong>{{ format_money($booking->amount_hunting) }}</strong></td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label fsz21">{{ __('booking.email.total') }}</td>
                            <td class="val fsz21"><strong style="color: #FA5636">{{ format_money($total) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label fsz21">{{ __('booking.email.paid') }}</td>
                            <td class="val fsz21"><strong style="color: #FA5636">{{ format_money($paid) }}</strong></td>
                        </tr>
                        @if($total > $paid)
                            <tr>
                                <td class="label fsz21">{{ __('booking.email.remain') }}</td>
                                <td class="val fsz21"><strong style="color: #FA5636">{{ format_money($total - $paid) }}</strong></td>
                            </tr>
                        @endif
                    </table>
                </div>

                @if($emailType === 'customer')
                    <div class="text-center mt20">
                        <a href="{{ $bookingsUrl }}" target="_blank" class="btn btn-primary">{{ __('booking.email.manage_bookings') }}</a>
                    </div>
                @endif
            @endif
        </div>

        @if($emailType === 'admin')
            <div class="b-panel">
                <div class="b-panel-title">{{ __('booking.email.customer_information') }}</div>
                <div class="b-table-wrap">
                    <table class="b-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="label">{{ __('booking.email.first_name') }}</td>
                            <td class="val">{{ $customerFirstName }}</td>
                        </tr>
                        <tr>
                            <td class="label">{{ __('booking.email.last_name') }}</td>
                            <td class="val">{{ $customerLastName }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
