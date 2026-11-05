<?php

namespace Modules\Booking\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Animals\Models\Animal;

class BookingService extends Model
{
    public const string PLACE_BASE = 'base';

    protected $table = 'bc_booking_services';

    protected $fillable = [
        'booking_id',
        'service_type',
        'calculation_type',
        'service_id',
        'hunter_id',
        'animal_id',
        'type',
        'count',
        'price',
        'comment',
        'place',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
    public function hunter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hunter_id');
    }
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * Сумма строки уже включает количество раз. На число суток повторно не умножается.
     */
    public function foodLineAmount(): float
    {
        return round((float) $this->price, 2);
    }

    /**
     * Цена одного раза. Сумма строки — эта цена, умноженная на count.
     */
    public function foodUnitPrice(): float
    {
        $count = max(1, (int) $this->count);

        return round((float) $this->price / $count, 2);
    }
}
