@php
    $booking->loadMissing(['hotel.location', 'roomsBooking.room', 'animal']);
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
    $guestCount = (int) ($booking->total_guests ?: $adults ?: 0);
    $huntersCount = (int) ($booking->total_hunting ?? 0);
    $paid = (float) ($booking->paid ?? 0);
    $total = (float) ($booking->total ?? 0);
    $siteUrl = rtrim((string) (setting_item('site_url') ?: config('app.url')), '/');
    $hotelUrl = ($hotel?->location?->slug && $hotel?->slug)
        ? $siteUrl.'/hotel/'.$hotel->location->slug.'/'.$hotel->slug
        : null;
    $address = $hotel?->address ?: $hotel?->location?->name;
@endphp
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
                                @php
                                    $roomTotal = (float) $room->price * (int) $room->number;
                                @endphp
                                <tr>
                                    <td class="label">{{ $room->room?->title }} * {{ $room->number }} :</td>
                                    <td class="val no-r-padding">
                                        <strong>{{ format_money($roomTotal) }}</strong>
                                        @if($guestCount > 1)
                                            <div style="font-weight:normal;font-size:12px;color:#6c757d;">
                                                {{ format_money(round($roomTotal / $guestCount, 2)) }} {{ __('booking.email.per_person') }}
                                            </div>
                                        @endif
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
                    <td class="val">
                        <strong>{{ format_money($booking->amount_hunting) }}</strong>
                        @if($huntersCount > 1)
                            <div style="font-weight:normal;font-size:12px;color:#6c757d;">
                                {{ format_money(round((float) $booking->amount_hunting / $huntersCount, 2)) }} {{ __('booking.email.per_person') }}
                            </div>
                        @endif
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
@endif
