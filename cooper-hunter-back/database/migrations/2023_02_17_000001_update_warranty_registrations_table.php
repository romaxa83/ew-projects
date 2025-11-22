<?php

use App\Models\Orders\Dealer\Order;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(WarrantyRegistration::TABLE,
            static function (Blueprint $table) {
                $table->string('hash', 32)->nullable();
            }
        );

        Schema::table(WarrantyRegistrationDeleted::TABLE,
            static function (Blueprint $table) {
                $table->string('hash', 32)->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(WarrantyRegistration::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('hash');
            }
        );

        Schema::table(WarrantyRegistrationDeleted::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('hash');
            }
        );
    }
};


