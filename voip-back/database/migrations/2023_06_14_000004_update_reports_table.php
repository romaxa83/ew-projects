<?php

use App\Models\Reports\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Report::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('calls');
                $table->dropColumn('answered_calls');
                $table->dropColumn('dropped_calls');
                $table->dropColumn('transfer_calls');
                $table->dropColumn('wait');
                $table->dropColumn('total_time');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Report::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('calls')->default(0);
                $table->unsignedInteger('answered_calls')->default(0);
                $table->unsignedInteger('dropped_calls')->default(0);
                $table->unsignedInteger('transfer_calls')->default(0);
                $table->unsignedInteger('wait')->default(0);
                $table->unsignedInteger('total_time')->default(0);
            }
        );
    }
};
