<?php

use App\Models\Commercial\CommercialProject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CommercialProject::TABLE,
            static function (Blueprint $table) {
                $table->timestamp('start_pre_commissioning_date')->nullable();
                $table->timestamp('end_pre_commissioning_date')->nullable();
                $table->timestamp('start_commissioning_date')->nullable();
                $table->timestamp('end_commissioning_date')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(CommercialProject::TABLE, function (Blueprint $table) {
            $table->dropColumn([
                'start_pre_commissioning_date',
                'end_pre_commissioning_date',
                'start_commissioning_date',
                'end_commissioning_date',
            ]);
        });
    }
};

