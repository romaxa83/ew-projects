<?php

use App\Enums\Tickets\TicketStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'tickets',
            static function (Blueprint $table) {
                $table->id();
                $table->uuid('guid')->unique();
                $table->enum('status', TicketStatusEnum::getValues());
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
