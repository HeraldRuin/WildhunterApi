<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hotel\Models\Hotel;
use Modules\Media\Helpers\FileHelper;

class Animal extends Model
{
    use SoftDeletes;

    public const string SERVICE_TROPHIES = 'trophies';
    public const string SERVICE_FINES = 'fines';
    public const string SERVICE_PREPARATIONS = 'preparations';

    public const string HUNT_TYPE_INDIVIDUAL = 'individual';
    public const string HUNT_TYPE_GROUP = 'group';

    protected $table = 'bc_animals';

    protected $fillable = [
        'title',
        'content',
        'status',
        'faqs',
        'hotel_id',
    ];

    protected $casts = [
        'hunt_individual' => 'boolean',
        'hunt_group' => 'boolean',
    ];

    public static function isEnable(): bool
    {
        return true;
    }

    public function getImageUrl($size = "medium")
    {
        $url = FileHelper::url($this->image_id, $size);
        return $url ? $url : '';
    }

    public function trophies(): HasMany
    {
        return $this->hasMany(AnimalTrophy::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(AnimalFine::class, 'animal_id', 'id');
    }

    public function preparations(): HasMany
    {
        return $this->hasMany(AnimalPreparation::class, 'animal_id', 'id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(AnimalPricePeriod::class);
    }

    public function huntTypeCode(): string
    {
        $isIndividual = $this->hunt_individual && !$this->hunt_group;

        return $isIndividual ? self::HUNT_TYPE_INDIVIDUAL : self::HUNT_TYPE_GROUP;
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'bc_hotel_animals', 'animal_id', 'hotel_id')
            ->withPivot('status', 'hunters_count', 'max_hunters_count');
    }

    public function scopeForHotel($query, int $hotelId)
    {
        return $query->join('bc_hotel_animals as bha', function ($join) use ($hotelId) {
            $join->on('bha.animal_id', '=', 'bc_animals.id')
                ->where('bha.hotel_id', '=', $hotelId);
        })
            ->select('bc_animals.*', 'bha.status as animal_status');
    }

    public function scopeForHotelWithService($query, int $hotelId, string $relation)
    {
        return $query
            ->join('bc_hotel_animals as bha', function ($join) use ($hotelId) {
                $join->on('bha.animal_id', '=', 'bc_animals.id')
                    ->where('bha.hotel_id', '=', $hotelId);
            })
            ->whereHas($relation, function ($q) use ($hotelId) {
                $q->whereHas('hotelPrices', function ($q2) use ($hotelId) {
                    $q2->where('hotel_id', $hotelId);
                });
            })
            ->select([
                'bc_animals.id',
                'bc_animals.title as title',
                'bha.status as animal_status',
            ])
            ->with([
                $relation => function ($q) use ($hotelId) {
                    $q->select('id', 'animal_id', 'type')
                        ->whereHas('hotelPrices', function ($q2) use ($hotelId) {
                            $q2->where('hotel_id', $hotelId);
                        })
                        ->with([
                            'hotelPrices' => function ($q2) use ($hotelId) {
                                $q2->where('hotel_id', $hotelId);
                            },
                        ]);
                },
            ]);
    }
}

