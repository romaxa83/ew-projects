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
                $table->string('guid', 36)->unique();
                $table->boolean('is_insert_asterisk')->default(false);
            });
    }

    public function down(): void
    {
        Schema::table(Department::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('guid');
                $table->dropColumn('is_insert_asterisk');
            });
    }
};
