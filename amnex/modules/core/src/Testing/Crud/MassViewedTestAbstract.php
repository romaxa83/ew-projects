<?php

namespace Wezom\Core\Testing\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;

abstract class MassViewedTestAbstract extends CrudTestAbstract
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
     * $this->executeCantDeleteByNotPermittedAdmin();
     * ```
     */
    abstract public function testCantDeleteByNotPermittedAdmin(): void;

    /**
     * Content:
     *
     * ```
     * $this->executeCantDeleteByNotAuthAdmin();
     * ```
     */
    abstract public function testCantDeleteByNotAuthAdmin(): void;

    protected function executeDoSuccess(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model = $this->createModel(true);
        $model2 = $this->createModel(true);
        $model3 = $this->createModel(true);

        $this->assertModelViewedStatus($model, true);

        $this->executeRequest([$model->getKey(), $model2->getKey()], false)
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        $this->assertModelViewedStatus($model, false);
        $this->assertModelViewedStatus($model2, false);
        $this->assertModelViewedStatus($model3, true);
    }

    protected function executeCantDeleteByNotPermittedAdmin(): void
    {
        $this->callLoginAsAdmin();

        $model = $this->createModel();

        $this->assertModelViewedStatus($model, false);

        $response = $this->executeRequest([$model->getKey()], true)->assertOk();

        $this->assertGraphQlForbidden($response);

        $this->assertModelViewedStatus($model, false);
    }

    protected function executeCantDeleteByNotAuthAdmin(): void
    {
        $model = $this->createModel();

        $this->assertModelViewedStatus($model, false);

        $response = $this->executeRequest([$model->getKey()], true)->assertOk();

        $this->assertGraphQlUnauthorized($response);

        $this->assertModelViewedStatus($model, false);
    }

    protected function executeRequest(array $ids, bool $viewed): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args(['ids' => $ids, 'viewed' => $viewed])
            ->select('message', 'type')
            ->executeAndReturnResponse();
    }

    protected function createModel(bool $viewed = false): Model
    {
        $modelName = $this->model();

        return $modelName::factory()->create([
            $this->fieldName() => $viewed ? now() : null,
        ]);
    }

    protected function assertModelViewedStatus(Model $model, bool $viewed): void
    {
        $fieldValue = $model->refresh()->{$this->fieldName()};

        if ($viewed) {
            $this->assertNotNull($fieldValue);
        } else {
            $this->assertNull($fieldValue);
        }
    }

    protected function permission(): string
    {
        return $this->basePermissionName() . '.view';
    }

    protected function fieldName(): string
    {
        return 'viewed_at';
    }
}
