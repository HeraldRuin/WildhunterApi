<?php

namespace Modules\Review\Controllers;

use Modules\Review\Models\Review;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Modules\Review\Dto\ReviewServiceData;
use Modules\Review\Services\ReviewService;
use Modules\Review\Http\Resources\ReviewResource;
use Modules\Review\Http\Requests\ServiceReviewRequest;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService)
    {
    }

    public function ratings(): array
    {
        return Review::RATINGS;
    }

    public function indexByService(ServiceReviewRequest $request, Review $review): JsonResponse
    {
        $dto = ReviewServiceData::fromRequest($request);
        $result = $this->reviewService->indexByService($review, $dto);

        return new SuccessResponse(data: ReviewResource::collection($result['reviews']));
    }
    public function serviceReviews(ServiceReviewRequest $request): JsonResponse
    {
        $dto = ReviewServiceData::fromRequest($request);
        $result = $this->reviewService->serviceReviews($dto);

        return new SuccessResponse(data: ReviewResource::collection($result['reviews']));
    }
}
