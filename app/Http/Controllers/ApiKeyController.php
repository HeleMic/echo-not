<?php

namespace App\Http\Controllers;

use App\DTO\ApiKeyDTO;
use App\Models\ApiKey;
use Illuminate\Http\Response;
use App\Services\ApiKeyService;
use App\Http\Resources\ApiKeyResource;
use App\Http\Requests\ApiKeys\StoreApiKeyRequest;
use App\Http\Requests\ApiKeys\UpdateApiKeyRequest;

class ApiKeyController extends Controller
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected readonly ApiKeyService $apiKeyService)
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
        $this->authorize('viewAny', ApiKey::class);

        $apiKeys = $this->apiKeyService->getAll(auth()->user());

        return ApiKeyResource::collection($apiKeys);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ApiKeys\StoreApiKeyRequest $request
     * @return \App\Http\Resources\ApiKeyResource
     */
    public function store(StoreApiKeyRequest $request): ApiKeyResource
    {
        $this->authorize('create', ApiKey::class);

        $dto = ApiKeyDTO::fromStoreRequest($request);

        $apiKey = $this->apiKeyService->create($dto);

        $resource = new ApiKeyResource($apiKey);
        $resource->response()->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_CREATED);

        return $resource;
    }

    /**
     * Get the specified resource from storage.
     *
     * @param  string $id
     * @return \App\Http\Resources\ApiKeyResource
     */
    public function show(ApiKey $apiKey): ApiKeyResource
    {
        $this->authorize('view', $apiKey);

        return new ApiKeyResource($apiKey);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateApiKeyRequest $request, ApiKey $apiKey)
    {
        $this->authorize('update', $apiKey);

        $dto = ApiKeyDTO::fromUpdateRequest($request);

        $apiKey = $this->apiKeyService->update($apiKey, $dto);

        return new ApiKeyResource($apiKey);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiKey $apiKey
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiKey $apiKey): Response
    {
        $this->authorize('delete', $apiKey);

        $this->apiKeyService->delete($apiKey);

        return response()->noContent();
    }

    public function revoke(ApiKey $apiKey): ApiKeyResource
    {
        $this->authorize('revoke', $apiKey);

        $apiKey = $this->apiKeyService->revoke($apiKey);

        return new ApiKeyResource($apiKey);
    }
}
