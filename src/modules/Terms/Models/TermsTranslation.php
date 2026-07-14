<?php

namespace Modules\Terms\Models;

use App\Models\BaseModel;

class TermsTranslation extends BaseModel
{
    protected $table = 'bc_terms_translations';
    protected $fillable = [
        'name',
        'content',
    ];
    protected array $cleanFields = [
        'content'
    ];
}
