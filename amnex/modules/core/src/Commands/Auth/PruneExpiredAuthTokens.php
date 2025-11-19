<?php

namespace Wezom\Core\Commands\Auth;

use Illuminate\Console\Command;
use Wezom\Core\Models\Auth\PersonalSession;

class PruneExpiredAuthTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:prune-expired-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune expired authentication tokens and related sessions.';

    public function handle(): int
    {
        $sessions = PersonalSession::query()
            ->whereHas('refreshTokens', function ($query) {
                $query->where('expires_at', '<', now());
            })
            ->with('device')
            ->lazyById();

        foreach ($sessions as $session) {
            /** @var PersonalSession $session */
            $session->clearDeviceFcmToken();

            // Removing sessions with all related tokens. Tokens should be deleted by ON DELETE CASCADE.
            $session->delete();
        }

        return static::SUCCESS;
    }
}
