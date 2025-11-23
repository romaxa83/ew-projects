<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mysqlAudit')
            ->table('audits', function (Blueprint $table) {
                $table->date('dispatch_truck_at')->nullable();
                $table->index([
                    'dispatch_truck_at',
                    'auditable_type',
                ]);
            });
    }

    public function down(): void
    {
        Schema::connection('mysqlAudit')
            ->table('audits', function (Blueprint $table) {
                $table->dropColumn('dispatch_truck_at');
                $table->dropIndex([
                    'dispatch_truck_at',
                    'auditable_type',
                ]);
            });
    }
};
