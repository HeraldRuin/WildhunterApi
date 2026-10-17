<?php

namespace Modules\User\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Weapon\Models\Caliber;
use Modules\Weapon\Models\WeaponType;

class UserWeapon extends Model
{
    protected $table = 'bc_user_weapons';

    protected $fillable = [
        'user_id',
        'hunter_billet_number',
        'hunter_license_number',
        'hunter_license_series',
        'hunter_license_date',
        'weapon_type_id',
        'caliber_id',
    ];

    protected $casts = [
        'hunter_license_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function type(): BelongsTo
    {
        return $this->belongsTo(WeaponType::class, 'weapon_type_id');
    }
    public function caliber(): BelongsTo
    {
        return $this->belongsTo(Caliber::class, 'caliber_id');
    }
}
