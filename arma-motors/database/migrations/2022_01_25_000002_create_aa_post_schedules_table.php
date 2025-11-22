<?php

use App\Models\AA\AAPost;
use App\Models\AA\AAPostSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(AAPostSchedule::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('post_id');
                $table->foreign('post_id')
                    ->references('uuid')
                    ->on(AAPost::TABLE)
                    ->onDelete('cascade');

                $table->timestamp('date');
                $table->timestamp('start_work');
                $table->timestamp('end_work');
                $table->boolean('work_day');

                $table->unique(['post_id', 'date']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(AAPostSchedule::TABLE);
    }
};
