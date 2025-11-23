<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->boolean('is_send_start')->default(false);
                $table->boolean('is_send_finish')->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('is_send_start');
                $table->dropColumn('is_send_finish');
            }
        );
    }
};
