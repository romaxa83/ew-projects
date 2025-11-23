<?php

namespace WezomCms\Core\Filter;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Route;
use WezomCms\Core\Contracts\Filter\FilterStateStorageInterface;
use WezomCms\Core\Contracts\Filter\RestoreFilterInterface;

class RestoreFilter implements RestoreFilterInterface
{
    /**
     * @var FilterStateStorageInterface
     */
    private $storage;

    /**
     * An array with arguments that don't need to be stored.
     */
    protected $skip;

    /**
     * RestoreFilterInterface constructor.
     *
     * @param  FilterStateStorageInterface  $storage
     * @param  array  $skip
     */
    public function __construct(
        FilterStateStorageInterface $storage,
        array $skip = ['_token', 'per_page', 'page', 'filter_form']
    ) {
        $this->storage = $storage;
        $this->skip = $skip;
    }

    /**
     * Store or restore previously saved filter.
     *
     * @param  Request  $request
     * @return RedirectResponse|null
     */
    public function handle(Request $request): ?RedirectResponse
    {
        $key = $this->generateKey();

        if ($request->has(self::RESET_FILTER_KEY)) {
            $this->storage->forget($key);

            return null;
        }

        if ($request->boolean('filter_form')) {
            $queryParams = $this->filteredParameters($request);
            if ($queryParams) {
                $this->storage->set($key, $queryParams);
            } else {
                $this->storage->forget($key);
            }

            return null;
        }

        if ($request->hasAny('per_page', 'page')) {
            return null;
        }

        $oldParams = array_sort($this->removeSkipParameters($this->storage->get($key)));
        if (
            !empty($oldParams)
            && $oldParams != array_sort($this->removeSkipParameters($request->query()))
        ) {
            // Build and redirect to new URL
            return redirect()->route(Route::currentRouteName(), Route::current()->parameters + $oldParams);
        }

        return null;
    }

    /**
     * @return string
     */
    protected function generateKey(): string
    {
        return md5(Route::currentRouteAction());
    }

    /**
     * @param  Request  $request
     * @return array
     */
    protected function filteredParameters(Request $request): array
    {
        $result = [];

        foreach ($this->removeSkipParameters((array)$request->query()) as $key => $val) {
            if ($val !== '' && $val !== null) {
                $result[$key] = $val;
            }
        }

        return $result;
    }

    /**
     * @param  array  $parameters
     * @return array
     */
    protected function removeSkipParameters(array $parameters): array
    {
        return array_except($parameters, $this->skip);
    }
}
