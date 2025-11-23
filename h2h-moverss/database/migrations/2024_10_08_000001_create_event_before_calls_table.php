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
            ->create(EventBeforeCall::TABLE, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('project_id')->nullable();
                $table->string('call_type', 10)->nullable();
                $table->unsignedBigInteger('call_date_microsecond')->nullable();
                $table->string('destination', 50)->nullable();
                $table->string('number_e164', 50)->nullable();
                $table->string('callers_number', 50)->nullable();
                $table->string('employee_ringostat_id', 20)->nullable();
                $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::RINGOSTAT)
            ->dropIfExists(EventBeforeCall::TABLE);
    }
};
