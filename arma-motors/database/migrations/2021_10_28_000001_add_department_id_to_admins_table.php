<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToAdminsTable extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')
                ->after('dealership_id')->nullable();
            $table->foreign('department_id')
                ->references('id')
                ->on('dealership_departments')
                ->index('idx_admins_department_id')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign('idx_admins_department_id');
            $table->dropColumn(['department_id']);
        });
    }
}
