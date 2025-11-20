<?php

use App\Models\Employees\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Employee::TABLE,
            function (Blueprint $table) {
                $table->string('status', 25)->nullable()->change();
            });
    }

    public function down(): void
    {
        Schema::table(Employee::TABLE,
            function (Blueprint $table) {
                $table->string('status', 15)->nullable()->change();
            });
    }
};


