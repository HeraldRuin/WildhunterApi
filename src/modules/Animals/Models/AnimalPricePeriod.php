<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalPricePeriod extends Model
{
    protected $table = 'bc_animal_price_periods';

    protected $fillable = [
        'animal_id',
        'start_date',
        'end_date',
        'price',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'price' => 'float',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
