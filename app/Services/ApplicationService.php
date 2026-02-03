<?php

namespace App\Services;

use App\Models\User;
use App\Models\Application;
use App\DTO\Applications\StoreApplicationDTO;
use App\DTO\Applications\UpdateApplicationDTO;
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
            return Application::paginate(config('constants.pagination.elements_for_page'));
        }

        return Application::ownedBy($user)->paginate(config('constants.pagination.elements_for_page'));
    }

    /**
     * Create a new application.
     *
     * @param  \App\DTO\Applications\StoreApplicationDTO $dto
     * @return \App\Models\Application
     */
    public function create(StoreApplicationDTO $dto): Application
    {
        return Application::create([
            'user_id' => $dto->userId,
            'name' => $dto->name,
            'description' => $dto->description,
        ]);
    }

    /**
     * Update an existing application by ID.
     *
     * @param  string $id
     * @param  \App\DTO\Applications\UpdateApplicationDTO $dto
     * @return \App\Models\Application
     */
    public function updateById(string $id, UpdateApplicationDTO $dto): Application
    {
        $application = Application::findOrFail($id);
        return $this->update($application, $dto);
    }

    /**
     * Update an existing application.
     *
     * @param  \App\Models\Application $application
     * @param  \App\DTO\Applications\UpdateApplicationDTO $dto
     * @return \App\Models\Application
     */
    public function update(Application $application, UpdateApplicationDTO $dto): Application
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
