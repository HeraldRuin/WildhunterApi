<?php

namespace Modules\Booking\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingServiceLog extends Model
{
    public const string ACTION_ADDED = 'added';

    public const string ACTION_UPDATED = 'updated';

    public const string ACTION_DELETED = 'deleted';

    protected $table = 'bc_booking_service_logs';

    protected $fillable = [
        'booking_id',
        'booking_service_id',
        'action',
        'service_type',
        'service_id',
        'title',
        'calculation_type',
        'count',
        'price',
        'hunter_id',
        'hunter_name',
        'animal_id',
        'animal_title',
        'comment',
        'place',
        'user_id',
        'user_name',
    ];

    protected $casts = [
        'price' => 'float',
        'count' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
