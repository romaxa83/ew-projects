<?php

use App\Models\Users\DriverLicense;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(DriverLicense::TABLE_NAME, function (Blueprint $table) {
            $table->string('issuing_date_as_str')->nullable();
            $table->string('expiration_date_as_str')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(DriverLicense::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('issuing_date_as_str');
            $table->dropColumn('expiration_date_as_str');
        });
    }
};
