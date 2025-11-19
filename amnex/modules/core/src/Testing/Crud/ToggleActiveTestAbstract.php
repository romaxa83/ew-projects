<?php

namespace Wezom\Core\Testing\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;

abstract class ToggleActiveTestAbstract extends CrudTestAbstract
{
    /**
     * Content:
     *
     * ```
     * $this->executeDoSuccess();
     * ```
     */
    abstract public function testDoSuccess(): void;

    /**
     * Content:
     *
     * ```
     * $this->executeReverseDoSuccess();
     * ```
     */
    abstract public function testReverseDoSuccess(): void;

    /**
     * Content:
     *
     * ```
     * $this->executeNotPermittedUserGetNoPermissionError();
     * ```
     */
    abstract public function testNotPermittedUserGetNoPermissionError(): void;

    /**
     * Content:
     *
     * ```
     * $this->executeGuestGetUnauthorizedError();
     * ```
     */
    abstract public function testGuestGetUnauthorizedError(): void;

    protected function executeDoSuccess(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model = $this->createModel();

        $this->assertModelStatus($model, false);

        $this->executeRequest($model->getKey())
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        $this->assertModelStatus($model, true);
    }

    protected function executeReverseDoSuccess(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model = $this->createModel(true);

        $this->assertModelStatus($model, true);

        $this->executeRequest($model->getKey())
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        $this->assertModelStatus($model, false);
    }

    protected function executeNotPermittedUserGetNoPermissionError(): void
    {
        $this->callLoginAsAdmin();

        $model = $this->createModel();

        $this->assertModelStatus($model, false);

        $response = $this->executeRequest($model->getKey())->assertOk();

        $this->assertGraphQlForbidden($response);

        $this->assertModelStatus($model, false);
    }

    protected function executeGuestGetUnauthorizedError(): void
    {
        $model = $this->createModel();

        $this->assertModelStatus($model, false);

        $response = $this->executeRequest($model->getKey())->assertOk();

        $this->assertGraphQlUnauthorized($response);

        $this->assertModelStatus($model, false);
    }

    protected function executeRequest(string ...$ids): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args(compact('ids'))
            ->select('message', 'type')
            ->executeAndReturnResponse();
    }

    protected function createModel(bool $active = false): Model
    {
        $modelName = $this->model();

        return $modelName::factory()->create(compact('active'));
    }

    protected function assertModelStatus(Model $model, bool $active): void
    {
        $this->assertDatabaseHas(
            $model,
            [
                $model->getKeyName() => $model->getKey(),
                'active' => $active,
            ]
        );
    }

    protected function permission(): string
    {
        return $this->basePermissionName() . '.update';
    }
}
