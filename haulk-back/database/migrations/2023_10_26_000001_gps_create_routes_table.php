<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->create(\App\Models\GPS\Route::TABLE_NAME, function (Blueprint $table) {
                $table->id();
                $table->bigInteger('truck_id')->nullable();
                $table->bigInteger('trailer_id')->nullable();
                $table->json('data');
                $table->timestamp('date');
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->dropIfExists(\App\Models\GPS\Route::TABLE_NAME);
    }
};

