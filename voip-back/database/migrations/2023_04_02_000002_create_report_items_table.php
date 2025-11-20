<?php

use App\Models\Reports;
use App\Models\Reports\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Reports\Item::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('report_id');
                $table->foreign('report_id')
                    ->on(Report::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('status', 20);
                $table->string('num');
                $table->string('name')->nullable();
                $table->unsignedInteger('wait')->default(0);
                $table->unsignedInteger('total_time')->default(0);
                $table->timestamp('call_at')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Reports\Item::TABLE);
    }
};
