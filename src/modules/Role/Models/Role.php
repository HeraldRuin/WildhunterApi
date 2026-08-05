<?php

namespace Modules\Role\Models;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
//use Modules\User\Helpers\PermissionHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;

//TODO убрать ненужные методы и избавиться от базовой модели
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
//    public function givePermission($permissions = []){
//        if(is_string($permissions)) $permissions = [$permissions];
//
//        foreach ($permissions as $item){
//            RolePermission::firstOrCreate([
//                'role_id'=>$this->id,
//                'permission'=>$item
//            ]);
//        }
//
//        $this->clearCachePermissions($permissions);
//    }

//    public function syncPermissions($permissions = []){
//        if(is_string($permissions)) $permissions = [$permissions];
//
//        $ids = [];
//        foreach ($permissions as $item){
//            $rp = RolePermission::firstOrCreate([
//                'role_id'=>$this->id,
//                'permission'=>$item
//            ]);
//
//            $ids[] = $rp->id;
//        }
//
//        RolePermission::query()->where('role_id',$this->id)->whereNotIn('id',$ids)->delete();
//
//        $this->clearCachePermissions();
//    }

//    public function clearCachePermissions($permissions = []){
//        if(empty($permissions)) $permissions = PermissionHelper::all();
//
//        foreach ($permissions as $p){
//            Cache::forget('role_'.$this->id.'_'.$p);
//        }
//    }

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
