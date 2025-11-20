<?php

use App\Models\Schedules\AdditionsDay;
use App\Models\Schedules\Schedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(AdditionsDay::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('schedule_id');
                $table->foreign('schedule_id')
                    ->on(Schedule::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(AdditionsDay::TABLE);
    }
};

