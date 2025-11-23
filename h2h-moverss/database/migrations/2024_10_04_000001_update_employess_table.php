<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Employee::TABLE, function (Blueprint $table) {
            $table->boolean('ringostat_sip_status')
                ->default(false)
            ;
        });
    }

    public function down(): void
    {
        Schema::table(Employee::TABLE, function (Blueprint $table) {
            $table->dropColumn('ringostat_sip_status')
            ;
        });
    }
};
