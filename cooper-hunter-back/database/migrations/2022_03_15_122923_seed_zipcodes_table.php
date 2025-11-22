<?php

use App\Services\Locations\ZipcodeService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (isTesting()) {
            return;
        }

        app(ZipcodeService::class)->import();
    }

    public function down(): void
    {
    }
};
