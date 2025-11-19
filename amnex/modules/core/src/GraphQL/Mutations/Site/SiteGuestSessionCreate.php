<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Mutations\Site;

use Throwable;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Models\Auth\GuestSession;

final class SiteGuestSessionCreate extends BaseFieldResolver
{
    public const NAME = 'siteGuestSessionCreate';

    /**
     * @throws Throwable
     */
    public function resolve(Context $context): GuestSession
    {
        $session = $this->findSession($context->getArg('session'));

        if (!$session) {
            $session = $this->createSession($context->getArg('session'));
        }

        return $session;
    }

    protected function findSession(string $session): ?GuestSession
    {
        return GuestSession::where('session', $session)->first();
    }

    protected function createSession(string $key): GuestSession
    {
        $session = new GuestSession();
        $session->session = $key;
        $session->expires_at = now()->addMonths(6);
        $session->save();

        return $session;
    }

    public function rules(array $args = []): array
    {
        return ['session' => ['required', 'string', 'max:255']];
    }
}
