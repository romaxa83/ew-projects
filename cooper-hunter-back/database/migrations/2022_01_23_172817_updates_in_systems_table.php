<?php

use App\Enums\Projects\Systems\WarrantyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'systems',
            static function (Blueprint $table) {
                $table->string('warranty_status')
                    ->default(WarrantyStatus::WARRANTY_NOT_REGISTERED)
                    ->after('project_id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'systems',
            static function (Blueprint $table) {
                $table->dropColumn('warranty_status');
            }
        );
    }
};
