@php
    $booking->loadMissing('creator');
    $creator = $booking->creator;
    $customerFirstName = $booking->first_name ?: $creator?->first_name;
    $customerLastName = $booking->last_name ?: $creator?->last_name;
@endphp
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
            @if(!empty($showCustomerNotes) && filled(trim((string) ($booking->customer_notes ?? ''))))
                <tr>
                    <td class="label">{{ __('booking.email.customer_notes') }}</td>
                    <td class="val">{!! nl2br(e($booking->customer_notes)) !!}</td>
                </tr>
            @endif
        </table>
    </div>
</div>
