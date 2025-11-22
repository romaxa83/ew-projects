<?php

namespace App\Console\Commands;

use App\Models\Orders\OrderSignature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteOldSignatureTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-old-signature-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting old signatures token which are expired.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        OrderSignature::where(
                'created_at',
                '<',
                Carbon::now()->subSeconds(config('orders.inspection.signature_bol_link_life'))
            )->delete();
        return 0;
    }
}
