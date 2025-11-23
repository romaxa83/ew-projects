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
                $table->string('group', 500)->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('group');
            }
        );
    }
};
