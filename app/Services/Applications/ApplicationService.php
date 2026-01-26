<?php

namespace App\Services\Applications;

use Str;
use App\Models\User;
use App\Models\Application;
use App\DTO\Applications\ApplicationDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all applications.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll(User $user): LengthAwarePaginator
    {
        if ($user->isAdmin()) {
            return Application::paginate(10);
        }

        return Application::ownedBy($user)->paginate(15);
    }

    /**
     * Create a new application.
     *
     * @param  \App\DTO\Applications\ApplicationDTO $dto
     * @return \App\Models\Application
     */
    public function create(ApplicationDTO $dto): Application
    {
        if ($dto->id === null) {
            $dto = $dto->withApiKey(
                config('application.api_key.prefix') . Str::random(config('application.api_key.length'))
            );
        }

        return Application::create([
            'name' => $dto->name,
            'user_id' => $dto->userId,
            'description' => $dto->description,
            'api_key' => $dto->apiKey,
        ]);
    }

    /**
     * Get an application by ID.
     *
     * @param  string $id
     * @return \App\Models\Application
     */
    public function find(string $id): Application
    {
        return Application::findOrFail($id);
    }

    /**
     * Update an existing application by ID.
     *
     * @param  string $id
     * @param  \App\DTO\Applications\ApplicationDTO $dto
     * @return \App\Models\Application
     */
    public function updateById(string $id, ApplicationDTO $dto): Application
    {
        $application = Application::findOrFail($id);
        return $this->update($application, $dto);
    }

    /**
     * Update an existing application.
     *
     * @param  \App\Models\Application $application
     * @param  \App\DTO\Applications\ApplicationDTO $dto
     * @return \App\Models\Application
     */
    public function update(Application $application, ApplicationDTO $dto): Application
    {
        $application->update([
            'name' => $dto->name,
            'description' => $dto->description,
        ]);
        return $application;
    }

    /**
     * Delete an existing application by ID.
     *
     * @param  string $id
     * @return void
     */
    public function deleteById(string $id): void
    {
        $application = Application::findOrFail($id);
        $this->delete($application);
    }

    /**
     * Delete an existing application.
     *
     * @param  \App\Models\Application $application
     * @return void
     */
    public function delete(Application $application): void
    {
        $application->delete();
    }

}
