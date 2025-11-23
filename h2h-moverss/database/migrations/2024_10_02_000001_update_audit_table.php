<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mysqlAudit')
            ->table('audits', function (Blueprint $table) {
                $table->unsignedInteger('division_id')
                    ->nullable();
            });
    }

    public function down(): void
    {
        Schema::connection('mysqlAudit')
            ->table('audits', function (Blueprint $table) {
                $table->dropColumn('division_id');
            });
    }
};
