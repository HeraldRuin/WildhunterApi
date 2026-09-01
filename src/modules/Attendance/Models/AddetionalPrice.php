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
        'is_system',
        'calculation_type',
        'count',
    ];

    protected $casts = [
        'is_system' => 'boolean',
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

    public function scopeAdditional(Builder $query): Builder
    {
        return $query
            ->where('is_system', false);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query
                ->where('is_system', true);
        });
    }

    public function scopeAccessible(Builder $query, int $hotelId, int $userId): Builder
    {
        return $query
            ->where('hotel_id', $hotelId)
            ->where('user_id', $userId);
    }

    public function isSystem(): bool
    {
        return (bool) $this->is_system;
    }

    public function isAdditional(): bool
    {
        return !$this->isSystem();
    }

    public function isFood(): bool
    {
        return $this->type === self::FOOD || $this->name === self::FOOD_NAME;
    }
}
