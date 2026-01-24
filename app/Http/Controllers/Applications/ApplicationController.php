<?php

namespace App\Http\Controllers\Applications;

use App\Models\Application;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\DTO\Applications\ApplicationDTO;
use App\Services\Applications\ApplicationService;
use App\Http\Resources\Applications\ApplicationResource;
use App\Http\Requests\Applications\StoreApplicationRequest;

class ApplicationController extends Controller
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected readonly ApplicationService $applicationService)
    {
        //
    }

    /**
     * Get all the resources in the storage.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->authorize('viewAny');

        $applications = $this->applicationService->getAll(auth()->user());

        return ApplicationResource::collection($applications);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Applications\StoreApplicationRequest $request
     * @return \App\Http\Resources\Applications\ApplicationResource
     */
    public function store(StoreApplicationRequest $request): ApplicationResource
    {
        $this->authorize('create');

        $dto = ApplicationDTO::fromStoreRequest($request);
        $dto->userId = $request->user()->id;

        $application = $this->applicationService->create($dto);

        return new ApplicationResource($application);
    }

    /**
     * Get the specified resource from storage.
     *
     * @param  string $id
     * @return \App\Http\Resources\Applications\ApplicationResource
     */
    public function show(Application $application): ApplicationResource
    {
        $this->authorize('view', $application);

        $application = $this->applicationService->find($application->id);

        return new ApplicationResource($application);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Applications\StoreApplicationRequest $request
     * @param  \App\Models\Application $application
     * @return \App\Http\Resources\Applications\ApplicationResource
     */
    public function update(StoreApplicationRequest $request, Application $application): ApplicationResource
    {
        $this->authorize('update', $application);

        $dto = ApplicationDTO::fromStoreRequest($request);

        $application = $this->applicationService->update($application, $dto);

        return new ApplicationResource($application);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Application $application
     * @return \Illuminate\Http\Response
     */
    public function destroy(Application $application): Response
    {
        $this->authorize('delete', $application);

        $this->applicationService->delete($application);

        return response()->noContent();
    }
}
