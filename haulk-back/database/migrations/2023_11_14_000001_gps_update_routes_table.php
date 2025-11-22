<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\Alert;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\Route::TABLE_NAME, function (Blueprint $table) {
                $table->string('coords_hash', 50)->nullable();
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(\App\Models\GPS\Route::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn('coords_hash');
            });
    }
};
