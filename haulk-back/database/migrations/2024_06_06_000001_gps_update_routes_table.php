<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\Route::TABLE_NAME, function (Blueprint $table) {
                $table->integer('last_point_id', )->nullable();
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\Route::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn('last_point_id');
            });
    }
};
