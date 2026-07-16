<?php

namespace Modules\Hotel\Controllers;

use App\Exceptions\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Hotel\Dto\HotelFilterData;
use Modules\Hotel\Dto\HotelSearchData;
use App\Http\Responses\SuccessResponse;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Services\HotelService;
use App\Http\Resources\PaginateResource;
use Modules\Hotel\Http\Resources\HotelResource;
use Modules\Hotel\Http\Request\HotelFilterRequest;
use Modules\Hotel\Http\Request\HotelSearchRequest;
use Modules\Hotel\Http\Resources\HotelSearchResource;

class HotelController extends Controller
{
    public function __construct(private HotelService $hotelService)
    {
    }
    public function getHotels(HotelFilterRequest $request): JsonResponse
    {
        $dto = HotelFilterData::fromRequest($request);
        $result = $this->hotelService->getHotels($dto);

        return new SuccessResponse(
            data: HotelResource::collection($result['data'])
        );
    }

    /**
     * @throws ValidationException
     */
    public function getHotel($location, $slug): JsonResponse
    {
        $result = $this->hotelService->getHotel($location, $slug);

        return new SuccessResponse(
            data: new HotelResource($result['data'])
        );
    }

    public function searchHotels(HotelSearchRequest $request): JsonResponse
    {
        $dto = HotelSearchData::fromRequest($request);
        $result = $this->hotelService->searchHotels($dto);

        return new SuccessResponse(
            data: new PaginateResource($result['data'], HotelSearchResource::class)
        );
    }

    public function priceRange(): JsonResponse
    {
        return new SuccessResponse(data: Hotel::getMinMaxPrice());
    }
}
