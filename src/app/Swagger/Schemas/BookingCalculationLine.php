<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "BookingCalculationLine",
    required: ["name", "total_cost", "my_cost"],
    properties: [
        new OA\Property(
            property: "name",
            type: "string",
            example: "Проживание, 1 сутки"
        ),
        new OA\Property(
            property: "total_cost",
            type: "number",
            format: "float",
            example: 5000
        ),
        new OA\Property(
            property: "my_cost",
            type: "number",
            format: "float",
            example: 2500
        ),
        new OA\Property(
            property: "count",
            description: "Количество услуг в строке по всем охотникам. Для сгруппированного питания — сумма count. У проживания и итогов поля нет.",
            type: "integer",
            example: 3,
            nullable: true
        ),
        new OA\Property(
            property: "my_count",
            description: "Количество услуг текущего охотника. У индивидуальной услуги и штрафа — только его строки, у общей — то же, что count.",
            type: "integer",
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: "due_to_me",
            description: "Доля остальных охотников по своей трате. У чужой траты null.",
            type: "number",
            format: "float",
            example: 3750,
            nullable: true
        ),
        new OA\Property(
            property: "place",
            description: "Место оказания из bc_booking_services.place. Для питания и системной услуги записывается из каталога в момент добавления в бронь. base — на базе.",
            type: "string",
            example: "base"
        ),
        new OA\Property(
            property: "has_tooltip",
            type: "boolean",
            example: true
        ),
    ],
    type: "object"
)]
class BookingCalculationLine {}
