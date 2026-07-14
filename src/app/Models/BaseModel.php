<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasTranslations;

    protected $dateFormat    = 'Y-m-d H:i:s';

    public static function getModelName()
    {

    }
    public function findById($id)
    {
        return $this->find($id);
    }

    public static function getTableName()
    {
        return with(new static)->table;
    }
}
