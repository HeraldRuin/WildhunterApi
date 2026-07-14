<?php

namespace Modules\Attributes\Services;

use Modules\Attributes\Models\Attributes;

class AttributeService
{
    public function getAttributes($dto): array
    {
        $data = Attributes::where('service', $dto->type)->orderBy("position", "ASC")->with(['terms'=>function($query){
//            $query->withCount('hotel');
        },'translation'])->get();

        return [
            'code' => '',
            'data' => $data
        ];
    }
}
