<?php

use App\Helpers\DbConnections;
use App\Models\Ringostat\EventBeforeCall;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::connection(DbConnections::RINGOSTAT)
            ->table(EventBeforeCall::TABLE, function (Blueprint $table) {
                $table->dateTime('call_date')->nullable();
                $table->string('call_id', 50)->nullable();
                $table->string('extension_number', 20)->nullable();
                $table->string('responsible_employees')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::RINGOSTAT)
            ->table(EventBeforeCall::TABLE, function (Blueprint $table) {
                $table->dropColumn('call_date');
                $table->dropColumn('call_id');
                $table->dropColumn('extension_number');
                $table->dropColumn('responsible_employees');
            });
    }
};
