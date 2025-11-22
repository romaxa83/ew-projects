<?php

use App\Enums\Tickets\TicketStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'tickets',
            static function (Blueprint $table) {
                $table->enum('status', TicketStatusEnum::getValues())->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'tickets',
            static function (Blueprint $table) {
                $table->enum('status', TicketStatusEnum::getValues())->change();
            }
        );
    }
};
