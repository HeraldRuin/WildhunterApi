<?php

namespace App\Swagger\Tags;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Rooms",
    description: "Номера: поиск доступности, календарь кабинета, публикация, скрытие и удаление"
)]

class RoomsTag
{
}
