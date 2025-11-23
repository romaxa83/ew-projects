<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Employee::TABLE, function (Blueprint $table) {
            $table->unsignedInteger('zadarma_call_rec_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Employee::TABLE, function (Blueprint $table) {
            $table->dropColumn('zadarma_call_rec_id');
        });
    }
};
