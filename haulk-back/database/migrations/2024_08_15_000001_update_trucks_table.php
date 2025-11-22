<?php

use App\Models\Vehicles\Truck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Truck::TABLE_NAME, function (Blueprint $table) {
            $table->string('registration_date_as_str')->nullable();
            $table->string('registration_expiration_date_as_str')->nullable();
            $table->string('inspection_date_as_str')->nullable();
            $table->string('inspection_expiration_date_as_str')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Truck::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('registration_date_as_str');
            $table->dropColumn('registration_expiration_date_as_str');
            $table->dropColumn('inspection_date_as_str');
            $table->dropColumn('inspection_expiration_date_as_str');
        });
    }
};
