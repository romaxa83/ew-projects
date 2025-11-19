<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Models\Media;

abstract class DeleteFileTestAbstract extends CrudTestAbstract
{
    protected string $collection = 'default';

    /**
     * Content:
     *
     * ```
     * $this->executeNotAuthorizedAdminCantExecuteMutation();
     * ```
     */
    abstract public function testNotAuthorizedAdminCantExecuteMutation(): void;

    /**
     * Content:
     *
     * ```
     * $this->executeAdminWithoutRoleCantExecuteMutation();
     * ```
     */
    abstract public function testAdminWithoutRoleCantExecuteMutation(): void;

    /**
     * Content:
     *
     * ```
     * $this->executeDoSuccess();
     * ```
     */
    abstract public function testDoSuccess(): void;

    protected function createModel(): Model
    {
        $modelName = $this->model();

        return $modelName::factory()->create();
    }

    protected function createMedia(Model $model): Media
    {
        return Media::factory()
            ->toModel($model)
            ->toCollection($this->collection)
            ->create([
                'mime_type' => 'png',
                'file_name' => 'signed.png',
            ]);
    }

    protected function executeNotAuthorizedAdminCantExecuteMutation(): void
    {
        $this->executeUnauthorizedCheck();
    }

    protected function executeAdminWithoutRoleCantExecuteMutation(): void
    {
        $this->callLoginAsAdmin();

        $model = $this->createModel();
        $media = $this->createMedia($model);

        $this->assertMediaExists($media);

        $result = $this->executeRequest($model->getKey(), [$media->getKey()])
            ->assertOk();

        $this->assertGraphQlForbidden($result);

        $this->assertMediaExists($media);
    }

    protected function executeUnauthorizedCheck(): void
    {
        $model = $this->createModel();
        $media = $this->createMedia($model);

        $this->assertMediaExists($media);

        $result = $this->executeRequest($model->getKey(), [$media->getKey()])
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);

        $this->assertMediaExists($media);
    }

    protected function executeDoSuccess(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model = $this->createModel();
        $media = $this->createMedia($model);
        $media2 = $this->createMedia($model);

        $this->assertMediaExists($media);
        $this->assertMediaExists($media2);

        $this->executeRequest($model->getKey(), [$media->getKey()])
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        $this->assertMediaMissing($media);
        $this->assertMediaExists($media2);
    }

    protected function executeRequest(string|int $entityId, array $fileIds): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args(compact('entityId', 'fileIds'))
            ->select('message', 'type')
            ->executeAndReturnResponse();
    }

    protected function permission(): string
    {
        return $this->basePermissionName() . '.update';
    }

    protected function assertMediaExists(Media $media): void
    {
        $this->assertDatabaseHas(Media::class, [
            $media->getKeyName() => $media->getKey(),
            'collection_name' => $this->collection,
        ]);
    }

    protected function assertMediaMissing(Media $media): void
    {
        $this->assertDatabaseMissing(Media::class, [
            $media->getKeyName() => $media->getKey(),
        ]);
    }
}
