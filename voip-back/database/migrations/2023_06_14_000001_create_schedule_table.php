<?php

use App\Models\Schedules\Schedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Schedule::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Schedule::TABLE);
    }
};
