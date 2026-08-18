<?php

namespace App\Swagger\Tags;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Email',
    description: 'Отправка писем и формы обратной связи'
)]
class EmailTag
{
}
