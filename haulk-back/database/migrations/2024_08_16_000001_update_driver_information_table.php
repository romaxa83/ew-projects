<?php

use App\Models\Users\DriverInfo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(DriverInfo::TABLE_NAME, function (Blueprint $table) {
            $table->string('medical_card_issuing_date_as_str')->nullable();
            $table->string('medical_card_expiration_date_as_str')->nullable();
            $table->string('mvr_reported_date_as_str')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(DriverInfo::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('medical_card_issuing_date_as_str');
            $table->dropColumn('medical_card_expiration_date_as_str');
            $table->dropColumn('mvr_reported_date_as_str');
        });
    }
};
