<?php

namespace WezomCms\Users\Events;

use Illuminate\Queue\SerializesModels;

class AutoRegistered
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Automatically generated password.
     *
     * @var string|int
     */
    public $password;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param $password
     */
    public function __construct($user, $password)
    {
        $this->user = $user;

        $this->password = $password;
    }
}
