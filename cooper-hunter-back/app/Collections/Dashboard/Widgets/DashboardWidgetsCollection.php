<?php

namespace App\Collections\Dashboard\Widgets;

use App\Dashboard\Widgets\AbstractWidget;
use ArrayIterator;
use Illuminate\Support\Collection;

/**
 * @method AbstractWidget|null first(callable $callback = null, $default = null)
 * @method AbstractWidget|null last(callable $callback = null, $default = null)
 * @method AbstractWidget|null get($key, $default = null)
 * @method AbstractWidget|null pop()
 * @method AbstractWidget|null shift()
 * @method ArrayIterator|AbstractWidget[] getIterator()
 *
 * @property AbstractWidget[] items
 */
class DashboardWidgetsCollection extends Collection
{

}