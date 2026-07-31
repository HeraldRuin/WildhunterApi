<?php

namespace Modules\User\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\User\Support\UserAvatarUrl;

class UserLoginResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'first_name'=>$this->resource->first_name,
            'last_name'=>$this->resource->last_name,
            'email'=>$this->resource->email,
            'avatar'=> UserAvatarUrl::resolve($this->resource->avatar_id),
        ];
    }
}
