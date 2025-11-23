<?php

namespace WezomCms\Catalog\Filter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use WezomCms\Catalog\Filter\Contracts\FilterFormBuilder;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\HandlerInterface;
use WezomCms\Catalog\Filter\Contracts\ResultFilteringInterface;
use WezomCms\Catalog\Filter\Contracts\SelectedAttributesInterface;
use WezomCms\Catalog\Filter\Contracts\StorageInterface;
use WezomCms\Catalog\Filter\Contracts\UrlBuilderInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectSortException;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\Exceptions\NeedRedirectException;

class Filter implements FilterInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;
    /**
     * @var array|HandlerInterface[]
     */
    protected $handlers = [];
    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * FilterInterface constructor.
     * @param  StorageInterface  $storage
     * @param  UrlBuilderInterface  $urlBuilder
     */
    public function __construct(StorageInterface $storage, UrlBuilderInterface $urlBuilder)
    {
        $this->storage = $storage;

        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @param  ResultFilteringInterface  $handler
     * @return FilterInterface
     */
    public function addHandler(ResultFilteringInterface $handler): FilterInterface
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * @param  iterable  $handlers
     * @return FilterInterface
     */
    public function addHandlers(iterable $handlers): FilterInterface
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws NeedRedirectException
     * @throws IncorrectUrlParameterException
     */
    public function start(): FilterInterface
    {
        $this->urlBuilder->start();

        $this->checkParameters();

        return $this;
    }

    /**
     * @param  int|null  $perPage
     * @param  array|string  $exceptHandlers
     * @param  array  $criteria
     * @return mixed
     */
    public function getFilteredItems(?int $perPage = null, $exceptHandlers = [], array $criteria = [])
    {
        $queryBuilder = $this->storage->beginSelection();

        $this->applyHandlers($queryBuilder, $exceptHandlers, $criteria);

        return $perPage !== null
            ? $queryBuilder->paginate($perPage)->appends(\Request::except('page'))
            : $queryBuilder->get();
    }

    /**
     * @param  array|string  $exceptHandlers
     * @param  array  $criteria
     * @return mixed|Builder
     */
    public function filteredItemsQuery($exceptHandlers = [], array $criteria = [])
    {
        $queryBuilder = $this->storage->beginSelection();

        $this->applyHandlers($queryBuilder, $exceptHandlers, $criteria);

        return $queryBuilder;
    }


    /**
     * @return mixed
     */
    public function countFilteredItems()
    {
        $queryBuilder = $this->storage->beginCount();

        $this->applyHandlers($queryBuilder);

        event('filter:count_filtered_items', $queryBuilder);

        return $queryBuilder->without('translation')->count();
    }

    /**
     * @param $criteria
     * @return bool
     */
    public function hasResultByCriteria($criteria): bool
    {
        $queryBuilder = $this->storage->beginCount();

        $this->applyHandlers($queryBuilder, [Sort::class], $criteria);

        event('filter:count_filtered_items', $queryBuilder);

        return $queryBuilder->without('translation')->exists();
    }

    /**
     * @param $column
     * @param  array  $exceptHandlers
     * @param  callable|null  $callback
     * @return mixed
     */
    public function getMinMaxFor($column, $exceptHandlers = [], callable $callback = null)
    {
        $queryBuilder = $this->storage->beginCount();

        $queryBuilder->select($column);

        $this->applyHandlers($queryBuilder, $exceptHandlers);

        if ($callback !== null) {
            call_user_func($callback, $queryBuilder);
        }

        // We need subquery to get proper values filtered by spec values
        return DB::table(DB::raw("({$queryBuilder->toSql()}) as sub"))
            ->mergeBindings($queryBuilder->toBase())
            ->selectRaw("MIN({$column}) as min_value, MAX({$column}) as max_value")
            ->first();
    }

    /**
     * @param  int  $limit
     * @return FilterInterface
     */
    public function setLimit(int $limit): FilterInterface
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @throws NeedRedirectException
     * @throws IncorrectUrlParameterException
     */
    public function checkParameters()
    {
        $urlParameters = (array) $this->urlBuilder->getParameters();

        // Validate parameter keys
        $supportedParameters = [];
        foreach ($this->handlers as $handler) {
            if ($handler instanceof HandlerInterface) {
                $supportedParameters = array_merge($supportedParameters, $handler->supportedParameters());
            }
        }
        if (array_diff(array_keys($urlParameters), $supportedParameters)) {
            throw new IncorrectUrlParameterException();
        }

        // Validate values in every parameter
        $newSorting = [];
        foreach ($this->handlers as $handler) {
            if ($handler instanceof HandlerInterface) {
                try {
                    $handler->validateParameters();
                } catch (IncorrectSortException $e) {
                    $newSorting = array_merge($newSorting, $e->getParameters());
                }
            }
        }

        // Manual check sort parameters and values
        try {
            $this->checkParametersSort($urlParameters, $newSorting);
        } catch (IncorrectSortException $e) {
            throw (new NeedRedirectException())
                ->setUrl($this->urlBuilder->generateUrl($e->getParameters()));
        }
    }

    /**
     * @return iterable
     */
    public function buildWidgetData(): iterable
    {
        $data = collect();

        foreach ($this->handlers as $handler) {
            if ($handler instanceof FilterFormBuilder) {
                $items = $handler->buildFormData($this);
                foreach ($items as $item) {
                    $data->push($item);
                }
            }
        }

        return $data->sortBy('sort');
    }

    /**
     * @return UrlBuilderInterface
     */
    public function getUrlBuilder(): UrlBuilderInterface
    {
        return $this->urlBuilder;
    }

    /**
     * @return iterable|Collection
     */
    public function getSelectedAttributes(): iterable
    {
        $result = collect();

        foreach ($this->handlers as $handler) {
            if ($handler instanceof SelectedAttributesInterface) {
                foreach ($handler->selectedAttributes() as $item) {
                    $result->push($item);
                }
            }
        }

        return $result;
    }

    /**
     * @param $queryBuilder
     * @param  array|mixed  $exceptHandlers
     * @param  array  $criteria
     */
    public function applyHandlers($queryBuilder, $exceptHandlers = [], array $criteria = [])
    {
        $exceptHandlers = is_array($exceptHandlers) ? $exceptHandlers : [$exceptHandlers];

        foreach ($this->handlers as $handler) {
            if (in_array(get_class($handler), $exceptHandlers)) {
                continue;
            }

            if (!empty($criteria) && $handler instanceof HandlerInterface) {
                $handleCriteria = array_intersect_key($criteria, array_flip($handler->supportedParameters()));
            } else {
                $handleCriteria = [];
            }

            $handler->filter($queryBuilder, $this, $handleCriteria);
        }
    }

    /**
     * @param  array  $originalParams
     * @param  array  $newSorting
     * @throws IncorrectSortException
     */
    private function checkParametersSort(array $originalParams, array $newSorting)
    {
        $handlersSorting = $originalParams + $newSorting;

        foreach ($originalParams as $key => $values) {
            $sorted = $values;
            sort($sorted);
            if ($values !== $sorted) {
                $handlersSorting[$key] = $sorted;
            }
        }

        ksort($handlersSorting);

        if ($originalParams !== $handlersSorting) {
            throw (new IncorrectSortException())->setCorrectSort($handlersSorting);
        }
    }

    /**
     * @param $queryBuilder
     * @param  array  $handlers
     */
    public function applyOnlyHandlers($queryBuilder, $handlers = [])
    {
        foreach ($this->handlers as $handler) {
            if (in_array(get_class($handler), $handlers)) {
                $handler->filter($queryBuilder, $this);
            }
        }
    }
}
