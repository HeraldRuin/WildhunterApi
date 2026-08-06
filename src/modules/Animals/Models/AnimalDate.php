<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalDate extends Model
{
    protected $table = 'bc_animal_dates';

    protected $fillable = [
        'target_id',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public static function getDatesInRanges(string $startDate, string $endDate, int $id)
    {
        return static::query()->where([
            ['start_date', '<=', $startDate],
            ['end_date', '>=', $endDate],
            ['target_id', '=', $id],
        ])->take(100)->get();
    }
}
