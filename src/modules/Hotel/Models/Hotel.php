<?php

namespace Modules\Hotel\Models;

use App\Observers\HotelObserver;
use Modules\Animals\Models\Animal;
use Modules\Review\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Terms\Models\Terms;
use Modules\User\Models\UserWishList;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Traits\HasDeposit;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hotel extends Bookable
{
    use SoftDeletes;
    use Notifiable;
    use HasDeposit;

    protected $translation_class = HotelTranslation::class;
    protected $table                              = 'bc_hotels';
    public string $type                               = 'hotel';
    protected $fillable      = [
        'title',
        'content',
        'status',
        'has_food',
        'collection_timer_hours',
        'bed_timer_hours',
        'paid_timer_hours',
    ];

    protected $casts = [
        'policy' => 'array',
        'extra_price' => 'array',
        'service_fee' => 'array',
        'surrounding' => 'array',
        'has_food' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected static function booted(): void
    {
        static::observe(HotelObserver::class);
    }

    public static function getModelName()
    {
        return __("hotel.name.model_name");
    }

    public static function isEnable(): bool
    {
        return setting_item('hotel_disable') == false;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'publish');
    }

    public static function getMinMaxPrice(): array
    {
        $result = static::query()
            ->where('status', 'publish')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return [
            'min_price' => $result?->min_price ?? 0,
            'max_price' => $result?->max_price ?? 100,
        ];
    }

    public function hasWishList(): HasOne
    {
        return $this->hasOne(UserWishList::class, 'object_id', 'id')->where('object_model', $this->type)->where('user_id', Auth::id() ?? 0);
    }

    /**
     * Исключает отели с заблокированными комнатами в диапазоне дат
     */
    public function scopeExcludeBlockedForDates(Builder $query, string $rangeStart, string $rangeEndForDays): Builder
    {
        $blockedHotelIds = DB::table('bc_hotel_rooms as r')
            ->join('bc_hotel_room_dates as d', function ($join) use ($rangeStart, $rangeEndForDays) {
                $join->on('d.target_id', '=', 'r.id')
                    ->whereBetween(DB::raw('DATE(d.start_date)'), [$rangeStart, $rangeEndForDays]);
            })
            ->select('r.parent_id')
            ->groupBy('r.parent_id')
            ->havingRaw('SUM(d.active) = 0')
            ->pluck('r.parent_id')
            ->all();

        if (!empty($blockedHotelIds)) {
            $query->whereNotIn('bc_hotels.id', $blockedHotelIds);
        }

        return $query;
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class, 'parent_id', 'id')->where('status', "publish");
    }
    public function hotelRooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class, 'parent_id', 'id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'object_id');
    }

    public function animals(): BelongsToMany
    {
        return $this->belongsToMany(Animal::class, 'bc_hotel_animals', 'hotel_id', 'animal_id')
            ->withPivot('status', 'hunters_count');
    }

    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(Terms::class, 'bc_hotel_term', 'target_id', 'term_id');
    }
}
