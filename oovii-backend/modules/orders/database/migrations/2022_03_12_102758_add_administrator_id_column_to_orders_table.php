<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdministratorIdColumnToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table) {
                $table->foreignId('provider_id')
                    ->nullable()
                    ->after('status_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table) {
                $table->dropForeign(['provider_id']);
                $table->dropColumn('provider_id');
            }
        );
    }
}
