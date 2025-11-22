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
            ->table(Alert::TABLE_NAME, function (Blueprint $table) {
                $table->string('alert_type', 50)->change();
            });
    }

    public function down(): void
    {
        Schema::connection(DbConnections::GPS)
            ->table(Alert::TABLE_NAME, function (Blueprint $table) {
                $table->string('alert_type', 20)->change();
            });
    }
};
