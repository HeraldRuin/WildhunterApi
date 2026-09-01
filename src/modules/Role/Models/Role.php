<?php

namespace Modules\Role\Models;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    const int SUPERADMIN_ID = 1;
    const string SUPERADMIN = 'superadmin';
    const string ADMIN = 'baseadmin';
    const string CUSTOMER = 'hunter';

    protected $table = 'core_roles';

    protected $fillable = [
        'code',
        'name'
    ];

    public function scopeWithoutSuperadmin($query)
    {
        return $query->where('code', '!=', self::SUPERADMIN);
    }

    /**
     * Check Role has Permission
     *
     * @param $permission
     * @return int
     */
    public function hasPermission($permission): int
    {
        return Cache::rememberForever('role_'.$this->id.'_' . $permission, function () use ($permission) {
            return RolePermission::query()->where([
                'role_id'=>$this->id,
                'permission'=>$permission
            ])->count(['id']);
        });
    }

    /**
     * Give permissions to Role
     *
     * @param string|array $permissions
     */

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class,'role_id');
    }

    public static function findOrCreate($name){
        return parent::firstOrCreate(['name'=>$name,'code'=>Str::slug($name,'_')]);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class,'role_id');
    }
}
