<?php

namespace Modules\Booking\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingHunterInvitation extends BaseModel
{
    use SoftDeletes;

    public const string STATUS_PENDING = 'pending';
    public const string STATUS_ACCEPTED = 'accepted';
    public const string STATUS_DECLINED = 'declined';
    public const string PREPAYMENT_PAID = 'paid';
    public const string PREPAYMENT_PENDING = 'pending';
    public const string PREPAYMENT_UNPAID = 'unpaid';

    protected $table = 'bc_booking_hunter_invitations';

    protected $fillable = [
        'booking_hunter_id',
        'hunter_id',
        'email',
        'invited',
        'status',
        'prepayment_paid',
        'prepayment_paid_status',
        'invited_at',
        'accepted_at',
        'declined_at',
        'invitation_token',
        'note',
    ];

    protected $casts = [
        'invited' => 'boolean',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public function bookingHunter(): BelongsTo
    {
        return $this->belongsTo(BookingHunter::class, 'booking_hunter_id');
    }

    public function hunter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hunter_id');
    }
}
