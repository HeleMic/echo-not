<?php

namespace App\Http\Controllers;

use App\DTO\NotificationDTO;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Http\Resources\NotificationResource;
use App\Http\Requests\StoreNotificationRequest;

class NotificationController extends Controller
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected readonly NotificationService $notificationService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationRequest $request): NotificationResource
    {
        $this->authorize('create', Notification::class);

        $dto = NotificationDTO::fromStoreRequest($request)
            ->with([
                'user_id' => $request->user()->id,
                'application_id' => '', // TODO get application ID from api key, post auth
            ]);

        $notification = $this->notificationService->create($dto);

        $resource = new NotificationResource($notification);
        $resource->response()->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_CREATED);

        return $resource;
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        //
    }
}
