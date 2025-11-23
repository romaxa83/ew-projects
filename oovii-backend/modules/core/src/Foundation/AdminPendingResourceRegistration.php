<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Routing\PendingResourceRegistration;

class AdminPendingResourceRegistration extends PendingResourceRegistration
{
    /**
     * Add settings method.
     *
     * @return $this
     */
    public function settings(): AdminPendingResourceRegistration
    {
        $this->with(__FUNCTION__);

        return $this;
    }

    /**
     * Add show method.
     *
     * @return $this
     */
    public function show(): AdminPendingResourceRegistration
    {
        $this->with(__FUNCTION__);

        return $this;
    }

    /**
     * Add softDeletes methods.
     *
     * @return $this
     */
    public function softDeletes(): AdminPendingResourceRegistration
    {
        $this->with('trashed', 'restore', 'forceDestroy');

        return $this;
    }


    /**
     * @param  string|mixed  $method
     * @return AdminPendingResourceRegistration
     */
    public function with($method): AdminPendingResourceRegistration
    {
        if (!isset($this->options['with'])) {
            $this->options['with'] = [];
        }

        foreach (is_array($method) ? $method : func_get_args() as $method) {
            $this->options['with'][] = $method;
        }

        return $this;
    }
}
