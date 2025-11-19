<?php

namespace Wezom\Core\Commands\Auth;

use Illuminate\Console\Command;
use Wezom\Core\Models\Auth\GuestSession;

class PruneExpiredGuestSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guest-sessions:prune-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune expired guest sessions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        GuestSession::query()
            ->where('expires_at', '<', now())
            ->delete();

        return static::SUCCESS;
    }
}
