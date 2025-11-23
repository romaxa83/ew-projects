<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Types\GenderType;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->string('gender', 20)->nullable();
                $table->boolean('start_counter')
                    ->after('start_at')
                    ->default(false);
                $table->boolean('end_counter')
                    ->after('end_at')
                    ->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('gender');
                $table->dropColumn('start_counter');
                $table->dropColumn('end_counter');
            }
        );
    }
};
