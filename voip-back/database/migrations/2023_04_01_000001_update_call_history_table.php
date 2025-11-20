<?php

use App\Models\Calls\History;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(History::TABLE,
            function (Blueprint $table) {
                $table->string('channel')
                    ->after('uniqueid')->nullable();
            });
    }

    public function down(): void
    {
        Schema::table(History::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('channel');
            });
    }
};


