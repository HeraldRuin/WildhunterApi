@extends('Email::layout')
@section('content')
    {{-- @var \Modules\Booking\Models\Booking $booking --}}
    {{-- @var string $to --}}
    {{-- @var \App\Models\User|null $user --}}
    @php
        $adminName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $adminName = $adminName ?: ($user->user_name ?? __('booking.email.base_admin'));
        $hunterName = __('booking.email.hunter');
        if (!empty($user)) {
            $hunterName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            $hunterName = $hunterName ?: ($user->user_name ?? __('booking.email.hunter'));
        }
    @endphp
    <div class="b-container">
        <div class="b-panel">
            @if($to === 'BaseAdmin')
                <h3 class="email-headline"><strong>{{ __('booking.email.hello', ['name' => $adminName]) }}</strong></h3>
                <p>{{ __('booking.email.collection_finished_admin_body') }}</p>
            @else
                <h3 class="email-headline"><strong>{{ __('booking.email.hello', ['name' => $hunterName]) }}</strong></h3>
                <p>{{ __('booking.email.collection_finished_hunter_body') }}</p>
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

            @if(($to ?? '') === 'BaseAdmin')
                @include('Booking::emails.parts.booking-details')
            @endif
        </div>

        @if(($to ?? '') === 'BaseAdmin')
            @include('Booking::emails.parts.panel-customer')
        @endif
    </div>
@endsection
