<?php

use App\Enums\Warranties\WarrantyType;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(WarrantyRegistration::TABLE,
            static function (Blueprint $table) {
                $table->string('type', 20)
                    ->after('warranty_status')
                    ->default(WarrantyType::RESIDENTIAL);
            }
        );
    }

    public function down(): void
    {
        Schema::table(WarrantyRegistration::TABLE, function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};




