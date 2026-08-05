<?php

namespace Modules\Booking\Models;

use App\Models\User;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHunter extends BaseModel
{
    use SoftDeletes;

    protected $table = 'bc_booking_hunters';

    protected $fillable = [
        'booking_id',
        'invited_by',
        'is_master',
        'creator_role',
        'note',
    ];

    protected $casts = [
        'is_master' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
