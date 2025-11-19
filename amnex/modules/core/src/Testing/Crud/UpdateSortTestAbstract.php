<?php

namespace Wezom\Core\Testing\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;

abstract class UpdateSortTestAbstract extends CrudTestAbstract
{
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

    /**
     * Content:
     *
     * ```
     * $this->executeDoSuccessWithOffset();
     * ```
     */
    abstract public function testDoSuccessWithOffset(): void;

    protected function createModel(int $sort, ?int $parentId = null): Model
    {
        $modelName = $this->model();

        $attributes = [$this->fieldName() => $sort];
        if ($parentId) {
            $attributes[$this->parentFieldName()] = $parentId;
        }

        return $modelName::factory()->create($attributes);
    }

    protected function executeNotAuthorizedAdminCantExecuteMutation(): void
    {
        $this->executeUnauthorizedCheck();
    }

    protected function executeAdminWithoutRoleCantExecuteMutation(): void
    {
        $this->callLoginAsAdmin();

        $model = $this->createModel(1);

        $result = $this->executeRequest([$model->only($model->getKeyName())])
            ->assertOk();

        $this->assertGraphQlForbidden($result);
    }

    protected function executeUnauthorizedCheck(): void
    {
        $model = $this->createModel(1);

        $result = $this->executeRequest([$model->only($model->getKeyName())])
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    protected function executeRequest(array $items, int $offset = 0): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args([
                $this->inputArgumentName() => [
                    'offset' => $offset,
                    'items' => $items,
                ],
            ])
            ->select('message', 'type')
            ->executeAndReturnResponse();
    }

    protected function executeDoSuccess(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model1 = $this->createModel(1);
        $model2 = $this->createModel(2);
        $model3 = $this->createModel(3);
        $model4 = $this->createModel(4);

        $otherModel = $this->createModel(50);

        $this->executeRequest([
            $model3->only($model3->getKeyName()),
            $model4->only($model4->getKeyName()),
            $model2->only($model2->getKeyName()),
            $model1->only($model1->getKeyName()),
        ])
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        $this->assertSavedModelSort($model3, 0);
        $this->assertSavedModelSort($model4, 1);
        $this->assertSavedModelSort($model2, 2);
        $this->assertSavedModelSort($model1, 3);

        $this->assertSavedModelSort($otherModel, 50);
    }

    protected function assertSavedModelSort(Model $model, int $sort, ?int $parentId = null): void
    {
        $attributes = [
            $model->getKeyName() => $model->getKey(),
            $this->fieldName() => $sort,
        ];
        if ($parentId) {
            $attributes[$this->parentFieldName()] = $parentId;
        }

        $this->assertDatabaseHas($this->model(), $attributes);
    }

    protected function executeDoSuccessWithOffset(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model1 = $this->createModel(1);
        $model2 = $this->createModel(2);

        $otherModel = $this->createModel(50);

        $this->executeRequest(
            [
                $model2->only($model2->getKeyName()),
                $model1->only($model1->getKeyName()),
            ],
            15
        )
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        $this->assertSavedModelSort($model2, 15);
        $this->assertSavedModelSort($model1, 16);

        $this->assertSavedModelSort($otherModel, 50);
    }

    protected function executeDoSuccessNested(): void
    {
        $this->callLoginAsAdminWithPermissions([$this->permission()]);

        $model1 = $this->createModel(1);
        $model2 = $this->createModel(2);
        $model3 = $this->createModel(3);

        $nestedModel1 = $this->createModel(0, $model1->getKey());
        $nestedModel2 = $this->createModel(1, $model1->getKey());
        $nestedModel3 = $this->createModel(2, $model1->getKey());

        $otherModel = $this->createModel(50);

        $this->executeRequest([
            [
                'id' => $model3->getKey(),
            ],
            [
                'id' => $model1->getKey(),
                'children' => [
                    $nestedModel3->only($nestedModel3->getKeyName()),
                    $nestedModel1->only($nestedModel1->getKeyName()),
                ],
            ],
            [
                'id' => $model2->getKey(),
                'children' => [
                    $nestedModel2->only($model2->getKeyName()),
                ],
            ],
        ])
            ->assertNoErrors()
            ->assertSuccessResponseMessage();

        $this->assertSavedModelSort($model3, 0);
        $this->assertSavedModelSort($model1, 1);
        $this->assertSavedModelSort($model2, 2);

        $this->assertSavedModelSort($nestedModel3, 0, $model1->getKey());
        $this->assertSavedModelSort($nestedModel1, 1, $model1->getKey());

        $this->assertSavedModelSort($nestedModel2, 0, $model2->getKey());

        $this->assertSavedModelSort($otherModel, 50);
    }

    protected function permission(): string
    {
        return $this->basePermissionName() . '.update';
    }

    protected function fieldName(): string
    {
        return 'sort';
    }

    protected function parentFieldName(): string
    {
        return 'parent_id';
    }

    protected function inputArgumentName(): string
    {
        return 'input';
    }
}
