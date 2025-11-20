<?php

use App\Models\Musics\Music;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Music::TABLE,
            function (Blueprint $table) {
                $table->boolean('has_unhold_data')->default(false);
                $table->json('unhold_data')->nullable();
            });
    }

    public function down(): void
    {
        Schema::table(Music::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('has_unhold_data');
                $table->dropColumn('unhold_data');
            });
    }
};

