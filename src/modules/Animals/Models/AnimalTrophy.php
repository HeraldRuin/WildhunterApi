<?php

namespace Modules\Animals\Models;

use App\Traits\HasHotelAnimalPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalTrophy extends Model
{
    use HasHotelAnimalPrice;

    protected $table = 'bc_animal_trophies';

    protected $fillable = [
        'animal_id',
        'type',
        'price',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
