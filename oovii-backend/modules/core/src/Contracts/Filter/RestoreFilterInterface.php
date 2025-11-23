<?php

namespace WezomCms\Core\Contracts\Filter;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface RestoreFilterInterface
{
    public const RESET_FILTER_KEY = 'reset_filter';

    /**
     * RestoreFilterInterface constructor.
     *
     * @param  FilterStateStorageInterface  $storage
     * @param  array  $skip
     */
    public function __construct(
        FilterStateStorageInterface $storage,
        array $skip = ['_token', 'per_page', 'page', 'filter_form']
    );

    /**
     * Store or restore previously saved filter.
     *
     * @param  Request  $request
     * @return RedirectResponse|null
     */
    public function handle(Request $request): ?RedirectResponse;
}
