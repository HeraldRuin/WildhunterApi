<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasTranslations
{

    /**
     * Class name for translation, default is current class
     * @var
     */
    protected $translation_class;

    /**
     * @internal will change to private
     */
    public function getTranslationModelName(): string
    {
        $class = $this->translation_class;

        if(!$class and class_exists(get_class($this).'Translation')){
            $class = get_class($this).'Translation';
        }
        return $class;
    }

    public function translation(): HasOne
    {
        return $this->hasOne($this->getTranslationModelName(),'origin_id')->where('locale', app()->getLocale());
    }
}
