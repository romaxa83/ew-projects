<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;

    public function __construct(Repository $config)
    {
        parent::__construct($config);

        $this->resolveTrustedProxies();
    }

    protected function resolveTrustedProxies(): void
    {
        if (
            is_null($proxies = config('app.trusted_proxies'))) {
            return;
        }

        $trusted = [];

        foreach (explode(',', $proxies) as $proxy) {
            $proxy = trim($proxy);

            if (empty($proxy)) {
                continue;
            }

            $trusted[] = $proxy;
        }

        $this->proxies = $trusted;
    }
}
