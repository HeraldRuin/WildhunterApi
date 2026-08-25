<?php

namespace Modules\Core\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Http\Requests\ListNotificationsRequest;
use Modules\Core\Http\Resources\NotificationResource;
use Modules\Core\Services\NotificationService;

class NotificationController
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $type = $request->string('type')->toString();
        $filterType = $type === 'all' ? null : $type;
        $paginator = $this->notificationService->listForUser(
            $user,
            $filterType,
            $request->integer('per_page', 20),
        );

        return new SuccessResponse(data: [
            'unread_count' => $this->notificationService->unreadCount($user),
            'notifications' => NotificationResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        return new SuccessResponse(data: [
            'unread_count' => $this->notificationService->unreadCount($user),
        ]);
    }

    public function markAsRead(string $notificationId): JsonResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $this->notificationService->markAsRead($notificationId, $user);

        return new SuccessResponse(
            code: 'marked_read',
            domain: 'notification',
            data: [
                'unread_count' => $this->notificationService->unreadCount($user),
            ],
        );
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $this->notificationService->markAllAsRead($user);

        return new SuccessResponse(
            code: 'marked_all_read',
            domain: 'notification',
            data: [
                'unread_count' => 0,
            ],
        );
    }
}
