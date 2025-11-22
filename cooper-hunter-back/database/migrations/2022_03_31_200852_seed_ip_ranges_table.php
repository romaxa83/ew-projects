<?php

use App\Services\Locations\IpRangeService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (isTesting()) {
            return;
        }

        app(IpRangeService::class)->import();
    }

    public function down(): void
    {
    }
};
