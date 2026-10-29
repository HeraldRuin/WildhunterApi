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
