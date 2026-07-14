<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
class TranslationResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'origin_id' => $this->origin_id,
            'locale' => $this->locale,
            'name' => $this->name,
            'content' => $this->content,
        ];
    }
}
