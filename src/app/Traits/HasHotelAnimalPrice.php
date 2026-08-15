<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Animals\Models\HotelAnimalPrice;

trait HasHotelAnimalPrice
{
    public function hotelPrices(): MorphMany
    {
        return $this->morphMany(HotelAnimalPrice::class, 'priceable');
    }
}
