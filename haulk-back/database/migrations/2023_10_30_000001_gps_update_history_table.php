<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\History;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(History::TABLE_NAME, function (Blueprint $table) {
                $table->boolean('is_speeding')->default(false);
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(History::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn('is_speeding');
            });
    }
};


