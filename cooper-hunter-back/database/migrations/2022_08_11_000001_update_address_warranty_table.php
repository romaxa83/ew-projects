<?php

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Warranty\Deleted\WarrantyAddressDeleted;
use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(WarrantyAddress::TABLE,
            static function (Blueprint $table) {
                $table->string('zip', 50)->change()
                ;
            }
        );
        Schema::table(WarrantyAddressDeleted::TABLE,
            static function (Blueprint $table) {
                $table->string('zip', 50)->change()
                ;
            }
        );
    }

    public function down(): void
    {
        Schema::table(WarrantyAddress::TABLE,
            static function (Blueprint $table) {
                $table->string('zip', 10)->change()
                ;
            }
        );
        Schema::table(WarrantyAddressDeleted::TABLE,
            static function (Blueprint $table) {
                $table->string('zip', 10)->change()
                ;
            }
        );
    }
};

