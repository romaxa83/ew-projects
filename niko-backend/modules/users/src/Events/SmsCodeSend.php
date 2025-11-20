<?php

namespace WezomCms\Users\Events;

use Illuminate\Queue\SerializesModels;

class SmsCodeSend
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
     * @var int
     */
    public $code;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param $password
     */
    public function __construct($user, $code)
    {
        $this->user = $user;

        $this->code = $code;
    }
}

