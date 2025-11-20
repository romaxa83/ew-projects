<?php

use App\Models\Schedules\Schedule;
use App\Models\Schedules\WorkDay;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(WorkDay::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('schedule_id');
                $table->foreign('schedule_id')
                    ->on(Schedule::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('name', 15);
                $table->string('start_work_time', 15)->nullable();
                $table->string('end_work_time', 15)->nullable();
                $table->boolean('active')->default(true);
                $table->integer('sort')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(WorkDay::TABLE);
    }
};
