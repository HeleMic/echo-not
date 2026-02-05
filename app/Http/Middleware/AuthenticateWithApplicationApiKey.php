<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiKey;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Services\ApiKeyService;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApplicationApiKey
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected readonly ApiKeyService $apiKeyService)
    {
        //
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('x-echonot-api-key')) {
            return response()->json(['message' => 'API key missing.'], Response::HTTP_UNAUTHORIZED);
        }

        $apiKey = $this->apiKeyService->findByPlainKey($request->header('x-echonot-api-key'));
        if ($apiKey === null || $apiKey->application === null) {
            return response()->json(['message' => 'Invalid API key.'], Response::HTTP_UNAUTHORIZED);
        }

        $this->apiKeyService->updateLastUsedAt($apiKey);

        $request->attributes->set('application', $apiKey->application);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
