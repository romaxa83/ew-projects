<?php

namespace Wezom\Cli\Support\IdeHelper;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Model;
use Wezom\Core\Traits\Model\Filterable;

class ModelHook implements ModelHookInterface
{
    public function run(ModelsCommand $command, Model $model): void
    {
        $this->writeFilterableMethod($model, $command);
    }

    public function writeFilterableMethod(Model $model, ModelsCommand $command): void
    {
        if (!class_exists(ModelFilter::class)) {
            return;
        }

        $modelName = get_class($model);

        $traits = class_uses_recursive($modelName);
        if (!in_array(Filterable::class, $traits)) {
            return;
        }

        /** @var Model|Filterable $model */
        /** @phpstan-ignore-next-line */
        $filter = '\\' . trim($model->getModelFilterClass(), '\\');
        if (!class_exists($filter)) {
            return;
        }

        $command->setMethod('newFilter', $filter, ['string $filter = null, array $input = []']);
    }
}
