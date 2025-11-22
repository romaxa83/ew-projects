<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\History::TABLE_NAME, function (Blueprint $table) {
                $table->index([
                    'received_at',
                    'company_id',
                    'truck_id',
                    'trailer_id',
                ]);
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\History::TABLE_NAME, function (Blueprint $table) {
                $table->dropIndex([
                    'received_at',
                    'company_id',
                    'truck_id',
                    'trailer_id',
                ]);
            });
    }
};
