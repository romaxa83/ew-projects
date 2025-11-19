<?php

declare(strict_types=1);

namespace Wezom\Core\Testing;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use UnitEnum;
use Wezom\Core\Enums\GraphQLErrorClassification;
use Wezom\Core\Models\Media;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use QueryInterceptor;
    use RefreshesSchemaCache;
    use WithFaker;

    private ?string $operationName = null;

    protected function operationName(): string
    {
        if ($this->operationName == null) {
            $this->operationName = str(get_called_class())
                ->classBasename()
                ->remove(['QueryTest', 'MutationTest', 'Test'])
                ->camel()
                ->value();
        }

        return $this->operationName;
    }

    protected function assertGraphQlUnauthorized(TestResponse $result): void
    {
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::UNAUTHORIZED);
    }

    protected function assertGraphQlForbidden(TestResponse $result): void
    {
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::FORBIDDEN);
    }

    protected function assertGraphQlServerError(TestResponse $result, ?string $message = null): void
    {
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::INTERNAL_ERROR);
        if ($message) {
            $this->assertGraphQlErrorMessage($result, $message);
        }
    }

    protected function assertGraphQlInternal(TestResponse $result, ?string $message = null): void
    {
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::INTERNAL_ERROR);
        $this->assertGraphQlErrorMessage($result, $message ?: __('core::exceptions.Something went wrong'));
    }

    protected function assertGraphQlErrorClassification(TestResponse $result, GraphQLErrorClassification $classification): void
    {
        self::assertEquals($classification->name, $result->json('errors.0.extensions.classification'));
    }

    protected function assertGraphQlErrorMessage(TestResponse $result, string $message): void
    {
        self::assertEquals($message, $result->json('errors.0.message'));
    }

    public function postGraphQL(array $data, array $headers = []): TestResponse
    {
        return $this->postJson(config('lighthouse.route.uri'), $data, $headers);
    }

    public function postGraphQlUpload(array $data, array $headers = []): TestResponse
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'multipart/form-data'];
        }

        return $this->post(config('lighthouse.route.uri'), $data, $headers);
    }

    protected function assertResponseHasValidationMessage(
        TestResponse $result,
        string $attribute,
        string|array $messages
    ): void {
        if (is_string($messages)) {
            $messages = [$messages];
        }

        $messages = array_map(fn ($message) => str_replace(':attribute', $attribute, $message), $messages);
        self::assertNotEmpty($messages, 'Expected validation messages must be specified.');

        $validationMessages = $result->json('errors.0.extensions.validation')[$attribute] ?? [];

        self::assertNotEmpty($validationMessages, 'Response doesnt contains validation errors.');

        foreach ($messages as $message) {
            $validationMessage = array_shift($validationMessages);
            self::assertEquals($message, $validationMessage);
        }
    }

    protected static function assertUnauthenticatedMessage(
        TestResponse $result
    ): void {
        self::assertEquals('Unauthenticated.', $result->json('message'));
    }

    protected function query(?string $name = null): GraphQLQueryExecutor
    {
        return GraphQLQueryExecutor::query($name ?? $this->operationName(), $this);
    }

    protected function queryPaginate(?string $name = null): GraphQLQueryExecutor
    {
        return GraphQLQueryExecutor::query($name ?? $this->operationName(), $this)->paginateMode();
    }

    protected function mutation(?string $name = null): GraphQLMutationExecutor
    {
        return GraphQLMutationExecutor::mutation($name ?? $this->operationName(), $this);
    }

    protected function upload(?string $name = null): GraphQLMutationExecutor
    {
        return GraphQLMutationExecutor::upload($name ?? $this->operationName(), $this);
    }

    protected function makeTranslationsArg(callable $translation): array
    {
        return languages()
            ->keys()
            ->map(static fn (string $locale) => array_merge($translation($locale), ['language' => $locale]))
            ->values()
            ->all();
    }

    protected function assertSameOrder(Collection|array $ids, ...$models): void
    {
        $ids = collect($ids)->map(static fn ($id) => (string)$id)->values()->all();
        $modelIds = collect($models)->map(static fn (Model $model) => (string)$model->getKey())->values()->all();

        $this->assertSameSize($ids, $modelIds);

        $this->assertEquals($ids, $modelIds);
    }

    protected function assertContainsAll(array $expectingItems, array $actualItems): void
    {
        foreach ($expectingItems as $item) {
            $this->assertContainsEquals($item, $actualItems);
        }
    }

    public function assertHasMedia(
        string $relatedModelName,
        string|int $relatedModelId,
        BackedEnum|UnitEnum|string $collectionName = 'default',
        int $generatedConversionsCount = 0
    ): void {
        $modelName = (new $relatedModelName())->getMorphClass();
        $collection = enum_to_string($collectionName);
        $messageAttributes = sprintf(
            'model_type: "%s", model_id: "%s", collection_name: "%s"',
            $modelName,
            $relatedModelId,
            $collection
        );

        $media = Media::query()
            ->where('model_id', $relatedModelId)
            ->where('model_type', $modelName)
            ->where('collection_name', $collection)
            ->first();

        $this->assertNotNull(
            $media,
            'No media found in database with fields: ' . $messageAttributes
        );

        $this->assertCount(
            $generatedConversionsCount,
            $media->generated_conversions,
            'Incorrect generated conversions count for media:' . $messageAttributes
        );
    }

    public function assertMissingMedia(
        string $relatedModelName,
        string|int $relatedModelId,
        BackedEnum|UnitEnum|string $collectionName = 'default'
    ): void {
        $this->assertDatabaseMissing(Media::class, [
            'model_id' => $relatedModelId,
            'model_type' => (new $relatedModelName())->getMorphClass(),
            'collection_name' => enum_to_string($collectionName),
        ]);
    }
}
