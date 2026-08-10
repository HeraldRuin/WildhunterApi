<?php

namespace Modules\Attendance\Models;

use App\Models\BaseModel;

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
        self::PERSON => 'Кол-во людей',
        self::INDIVIDUAL => 'Индивидуальный',
    ];

    public const string ADDETIONAL = 'addetional';
    public const string FOOD = 'food';
    public const string SPENDING = 'spending';
    public const string PREPARATION = 'preparation';
    public const string PENALTY = 'penalty';
    public const string TROPHY = 'trophy';
}
