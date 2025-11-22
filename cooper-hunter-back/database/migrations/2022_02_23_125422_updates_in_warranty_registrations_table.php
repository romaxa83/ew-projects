<?php

use App\Enums\Projects\Systems\WarrantyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'warranty_registrations',
            static function (Blueprint $table) {
                $table->string('warranty_status')
                    ->default(WarrantyStatus::PENDING)
                    ->after('id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'warranty_registrations',
            static function (Blueprint $table) {
                $table->dropColumn('warranty_status');
            }
        );
    }
};
