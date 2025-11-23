<?php

use App\Helpers\DbConnections;
use App\Models\Ringostat\EventAfterCall;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::connection(DbConnections::RINGOSTAT)
            ->table(EventAfterCall::TABLE, function (Blueprint $table) {
                $table->decimal('dialogue_quality_score', 10, 2)
                    ->nullable()
                ;
                $table->text('dialogue_quality_details')
                    ->nullable()
                ;
                $table->string('call_card_link', 1000)
                    ->nullable()
                ;
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::RINGOSTAT)
            ->table(EventAfterCall::TABLE, function (Blueprint $table) {
                $table->dropColumn('dialogue_quality_score');
                $table->dropColumn('dialogue_quality_details');
                $table->dropColumn('call_card_link');
            });
    }
};
