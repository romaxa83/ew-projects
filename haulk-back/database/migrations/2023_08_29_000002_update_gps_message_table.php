<?php

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\Message;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(Message::TABLE_NAME, function (Blueprint $table) {
                $table->json('data')->nullable();
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(Message::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn('data');
            });
    }
};


