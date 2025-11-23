<?php

use App\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::AUDIT)
            ->table('audits', function (Blueprint $table) {
                $table->boolean('is_client_activity')->default(false);
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::AUDIT)
            ->table('audits', function (Blueprint $table) {
                $table->dropColumn('is_client_activity');
            });
    }
};
