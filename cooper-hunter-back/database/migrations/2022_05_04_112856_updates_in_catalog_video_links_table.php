<?php

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_video_links',
            static function (Blueprint $table) {
                $table->enum('link_type', VideoLinkTypeEnum::getValues())
                    ->after('id')
                    ->default(VideoLinkTypeEnum::COMMON);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_video_links',
            static function (Blueprint $table) {
                $table->dropColumn('link_type');
            }
        );
    }
};
