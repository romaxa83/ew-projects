<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Category;
use WezomCms\Catalog\Models\Collections\Collection;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->string('type', 20)->default(Collection::TYPE_SOON);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('type');
            }
        );
    }
};
