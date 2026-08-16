@extends('Email::layout')
@section('content')
    {{-- @var \Modules\Booking\Models\Booking $booking --}}
    {{-- @var string $emailType --}}
    {{-- @var string $recipientName --}}
    {{-- @var \App\Models\User|null $baseAdmin --}}
    {{-- @var string|null $customMessage --}}
    @php
        $booking->loadMissing('creator');
        $emailType = $emailType ?? 'admin';
        if (!isset($recipientName) || $recipientName === '') {
            $recipientUser = $emailType === 'customer'
                ? $booking->creator
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
            <p>{{ __('booking.email.status_updated_admin_body') }}</p>

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

            @if(!empty($customMessage))
                <hr>
                <p>{{ $customMessage }}</p>
            @endif

            @include('Booking::emails.parts.booking-details')
        </div>

        @include('Booking::emails.parts.panel-customer')
    </div>
@endsection
