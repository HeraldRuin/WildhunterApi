<?php

namespace Modules\Hotel\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Auth;
use Modules\Hotel\Dto\CheckAvailabilityData;
use Modules\Hotel\Dto\HotelFilterData;
use Modules\Hotel\Dto\HotelSearchData;
use App\Http\Responses\SuccessResponse;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Services\HotelService;
use App\Http\Resources\PaginateResource;
use Modules\Hotel\Http\Resources\HotelResource;
use Modules\Hotel\Http\Request\CheckAvailabilityRequest;
use Modules\Hotel\Http\Request\HotelFilterRequest;
use Modules\Hotel\Http\Request\HotelSearchRequest;
use Modules\Hotel\Http\Resources\HotelOffersResource;
use Modules\Hotel\Http\Resources\HotelRoomResource;
use Modules\Hotel\Http\Resources\HotelManageEditResource;
use Modules\Hotel\Http\Resources\HotelManageListResource;
use Modules\Hotel\Http\Resources\HotelSearchResource;
use Modules\Hotel\Services\ManageHotelService;

class HotelController extends Controller
{
    public function __construct(
        private HotelService $hotelService,
        private ManageHotelService $manageHotelService,
    ) {
    }
    /**
     * @throws ForbiddenException
     */
    public function manageList(): JsonResponse
    {
        $hotels = $this->manageHotelService->list(Auth::user());

        return new SuccessResponse(
            data: HotelManageListResource::collection($hotels),
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function show(Hotel $hotel): JsonResponse
    {
        $hotel = $this->manageHotelService->show($hotel, Auth::user());

        return new SuccessResponse(
            data: new HotelManageEditResource($hotel),
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function destroy(Hotel $hotel): JsonResponse
    {
        $result = $this->manageHotelService->delete($hotel, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'hotel',
            data: $result['data'],
        );
    }

    public function getHotels(HotelFilterRequest $request): JsonResponse
    {
        $dto = HotelFilterData::fromRequest($request);
        $result = $this->hotelService->getHotels($dto);

        return new SuccessResponse(
            data: HotelOffersResource::collection($result['data'])
        );
    }


    /**
     * @throws NotFoundException
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

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function checkAvailability(CheckAvailabilityRequest $request): JsonResponse
    {
        $dto = CheckAvailabilityData::fromRequest($request);
        $result = $this->hotelService->checkAvailability($dto);

        return new SuccessResponse(
            data: [
                'rooms' => HotelRoomResource::collection($result['data'])->resolve(),
            ]
        );
    }
}
