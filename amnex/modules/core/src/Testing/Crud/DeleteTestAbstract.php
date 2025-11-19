<?php

namespace Wezom\Core\Testing\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\TestResponse;

abstract class DeleteTestAbstract extends CrudTestAbstract
{
    /**
     * Content:
     *
     * ```
     * $this->executeCantDeleteByNotAuthAdmin();
     * ```
     */
    abstract public function testCantDeleteByNotAuthAdmin(): void;

    /**
     * Content:
     *
     * ```
     * $this->executeCantDeleteByNotPermittedAdmin();
     * ```
     */
    abstract public function testCantDeleteByNotPermittedAdmin(): void;

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
     * $this->executeDeleteNotExisting();
     * ```
     */
    abstract public function testDeleteNotExisting(): void;

    protected function createModel(): Model
    {
        $modelName = $this->model();

        return $modelName::factory()->create();
    }

    protected function executeCantDeleteByNotAuthAdmin(): void
    {
        $this->executeUnauthorizedCheck();
    }

    protected function executeCantDeleteByNotPermittedAdmin(): void
    {
        $this->callLoginAsAdmin();

        $model = $this->createModel();

        $result = $this->executeRequest($model->getKey())
            ->assertOk();

        $this->assertGraphQlForbidden($result);
    }

    protected function executeUnauthorizedCheck(): void
    {
        $model = $this->createModel();

        $result = $this->executeRequest($model->getKey())
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    protected function executeDoSuccess(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model = $this->createModel();

        $this->assertDatabaseHas(
            $model,
            $model->only($model->getKeyName())
        );

        $this->executeRequest($model->getKey())
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        if (in_array(SoftDeletes::class, class_uses_recursive($this->model()))) {
            /** @phpstan-ignore-next-line */
            $this->assertNotNull($model->fresh()->{'deleted_at'});

            return;
        }

        $this->assertDatabaseMissing(
            $model,
            $model->only($model->getKeyName())
        );
    }

    protected function executeDeleteNotExisting(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $this->executeRequest(0)->assertNoErrors()->assertFailResponseMessage();
    }

    protected function executeRequest(string|int ...$ids): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args(compact('ids'))
            ->select('message', 'type')
            ->executeAndReturnResponse();
    }

    protected function permission(): string
    {
        return $this->basePermissionName() . '.delete';
    }
}
