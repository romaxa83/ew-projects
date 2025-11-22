<?php

use App\Models\Media\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Media::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('sort')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Media::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('sort');
            }
        );
    }
};
