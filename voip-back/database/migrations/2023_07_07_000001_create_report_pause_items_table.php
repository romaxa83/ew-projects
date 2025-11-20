<?php

use App\Models\Reports;
use App\Models\Reports\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Reports\PauseItem::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('report_id');
                $table->foreign('report_id')
                    ->on(Report::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->timestamp('pause_at');
                $table->timestamp('unpause_at')->nullable();
                $table->json('data');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Reports\PauseItem::TABLE);
    }
};
