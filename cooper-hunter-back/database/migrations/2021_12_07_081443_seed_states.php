<?php

use App\Services\Locations\StateService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        app(StateService::class)->seed();
    }

    public function down(): void
    {
    }
};
