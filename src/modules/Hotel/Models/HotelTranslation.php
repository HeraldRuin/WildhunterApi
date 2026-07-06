<?php

namespace Modules\Hotel\Models;

class HotelTranslation extends Hotel
{
    protected $table = 'bc_hotel_translations';

    protected $fillable = [
        'title',
        'content',
        'address',
        'policy',
        'surrounding'
    ];

    protected array $cleanFields = [
        'content'
    ];
    protected $casts = [
        'policy'  => 'array',
        'surrounding' => 'array',
    ];
}
