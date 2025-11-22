<?php

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\Models\Catalog\Videos\VideoLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(VideoLink::TABLE,
            static function (Blueprint $table) {
                $table->enum('link_type', VideoLinkTypeEnum::getValues())
                    ->after('id')
                    ->default(VideoLinkTypeEnum::COMMON)
                    ->change()
                ;
            }
        );
    }

    public function down(): void
    {
        Schema::table(VideoLink::TABLE,
            static function (Blueprint $table) {
                $table->enum('link_type', [
                    "common", "support"
                ])
                    ->after('id')
                    ->default(VideoLinkTypeEnum::COMMON)
                    ->change()
                ;
            }
        );
    }
};
