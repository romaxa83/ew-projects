<?php

use App\Models\Departments\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Department::TABLE,
            function (Blueprint $table) {
                $table->boolean('active')->default(true);
            });
    }

    public function down(): void
    {
        Schema::table(Department::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('active');
            });
    }
};
