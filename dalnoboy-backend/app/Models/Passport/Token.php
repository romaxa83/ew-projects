<?php

namespace App\Models\Passport;

use App\Traits\QueryCacheable;

class Token extends \Laravel\Passport\Token
{
    use QueryCacheable;

    public int $cacheFor = 300;

    public array $cacheTags = ['passport_token'];

    public string $cachePrefix = 'passport_token_';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->cacheFor = config('passport.cache.oauth_clients.duration');
    }
}
