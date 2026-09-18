<?php

namespace Modules\Hotel\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HuntingMethod extends BaseModel
{
    protected $table = 'bc_hunting_methods';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(
            Hotel::class,
            'bc_hotel_hunting_methods',
            'hunting_method_id',
            'hotel_id'
        );
    }
}
