<?php

use App\Helpers\DbConnections;
use App\Models\Ringostat\EventBeforeCall;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::connection(DbConnections::RINGOSTAT)
            ->table(EventBeforeCall::TABLE, function (Blueprint $table) {
                $table->unsignedInteger('client_id')
                    ->nullable()
                ;
        });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::RINGOSTAT)
            ->table(EventBeforeCall::TABLE, function (Blueprint $table) {
                $table->dropColumn('client_id');
            });
    }
};
