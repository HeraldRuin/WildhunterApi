<?php

namespace Modules\Attributes\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Modules\Attributes\Dto\AttributesServiceData;
use Modules\Attributes\Services\AttributeService;
use Modules\Attributes\Http\Resources\AttributesResource;
use Modules\Attributes\Http\Request\ServiceAttributesRequest;

class AttributesController extends Controller
{
    public function __construct(private AttributeService $attributeService)
    {
    }
    public function getHotelAttributes(ServiceAttributesRequest $request): JsonResponse
    {
        $dto = AttributesServiceData::fromRequest($request);
        $result = $this->attributeService->getAttributes($dto);

        return new SuccessResponse(
            data: AttributesResource::collection($result['data'])
        );
    }
}
