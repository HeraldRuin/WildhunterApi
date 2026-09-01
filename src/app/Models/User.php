<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Hotel\Models\Hotel;
use Modules\User\Support\UserAvatarUrl;
use Modules\Role\Models\Role;
use Modules\User\Models\UserWeapon;
use Modules\User\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'user_name',
        'email',
        'nik',
        'phone',
        'city',
        'address',
        'birthday',
        'hunter_billet_number',
        'password',
        'current_password',
        'status',
        'locale',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'created_at' => 'date',
        ];
    }
    public function updateFullName(): void
    {
        $this->name = trim("{$this->first_name} {$this->last_name}");
    }
    protected function roleName():Attribute{
        return Attribute::make(
            get:function(){
                return $this->role->name ?? '';
            }
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => UserAvatarUrl::resolve($this->avatar_id),
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class,'role_id');
    }

    public function weapons(): HasMany
    {
        return $this->hasMany(UserWeapon::class, 'user_id');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'admin_base');
    }
}
