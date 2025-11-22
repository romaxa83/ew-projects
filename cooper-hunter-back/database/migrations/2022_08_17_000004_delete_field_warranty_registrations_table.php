<?php

use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(WarrantyRegistration::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('address_info');
            }
        );
    }

    public function down(): void
    {
        Schema::table(WarrantyRegistration::TABLE,
            static function (Blueprint $table) {
                $table->json('address_info');
            }
        );
    }
};



