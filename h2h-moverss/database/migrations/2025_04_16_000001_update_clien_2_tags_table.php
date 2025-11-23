<?php

use App\Models\Client\ClientToTag;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(ClientToTag::TABLE, function (Blueprint $table) {
            $table->integer('employee_id')
                ->nullable()
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');
            $table->string('employee_name')->nullable();
            $table->timestamp('attached_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(ClientToTag::TABLE, function (Blueprint $table) {
            $table->dropColumn('employee_name');
            $table->dropColumn('employee_id');
            $table->dropColumn('attached_at');
        });
    }
};
