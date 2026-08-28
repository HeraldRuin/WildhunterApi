<?php

namespace Modules\Attendance\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class AddetionalPrice extends BaseModel
{
    protected $table = 'bc_addetional_prices';

    protected $fillable = [
        'user_id',
        'hotel_id',
        'name',
        'start_date',
        'end_date',
        'price',
        'type',
        'calculation_type',
        'count',
    ];

    public const string INDIVIDUAL = 'individual';
    public const string PERSON = 'per_person';

    public const array CALCULATION_TYPES = [
        self::PERSON => 'На человека',
        self::INDIVIDUAL => 'Индивидуально',
    ];

    public const string ADDETIONAL = 'addetional';
    public const string FOOD = 'food';
    public const string SPENDING = 'spending';
    public const string PREPARATION = 'preparation';
    public const string PENALTY = 'penalty';
    public const string TROPHY = 'trophy';

    public const string FOOD_NAME = 'Питание';

    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->where('hotel_id', $hotelId);
    }

    public function scopeAccessible(Builder $query, int $hotelId, int $userId): Builder
    {
        return $query
            ->where('hotel_id', $hotelId)
            ->where('user_id', $userId);
    }

    public function isFood(): bool
    {
        return $this->type === self::FOOD || $this->name === self::FOOD_NAME;
    }
}
