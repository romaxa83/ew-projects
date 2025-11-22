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
                $table->bigInteger('company_id')
                    ->index()
                    ->nullable();
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(History::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
    }
};



