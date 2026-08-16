@extends('Email::layout')
@section('content')
    {{-- @var \Modules\Booking\Models\Booking $booking --}}
    {{-- @var string $bodyText --}}
    {{-- @var bool $isInvitation --}}
    @php
        $inviterName = '';
        $creator = $booking->creator;
        if ($creator) {
            $inviterName = trim(($creator->first_name ?? '') . ' ' . ($creator->last_name ?? ''));
            $inviterName = $inviterName ?: ($creator->user_name ?? '');
        }
    @endphp
    <div class="b-container">
        <div class="b-panel">
            <h3 class="email-headline"><strong>{{ __('booking.email.hello', ['name' => __('booking.email.hunter')]) }}</strong></h3>

            @if(!empty($isInvitation))
                <div>{{ __('booking.email.invitation_body', ['name' => $inviterName]) }}</div>
            @else
                <div>{{ $bodyText }}</div>
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
        </div>
    </div>
@endsection
