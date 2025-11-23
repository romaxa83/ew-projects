<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Pages\Models\Page;
use WezomCms\Providers\Models\Provider;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Page::TABLE,
            static function (Blueprint $table) {
                $table->string('type', 50)->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Page::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn("type");
            }
        );
    }
};
