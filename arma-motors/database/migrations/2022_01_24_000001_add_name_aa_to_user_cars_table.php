<?php

use App\Models\User\Car;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Car::TABLE_NAME,
            static function (Blueprint $table) {
                $table->string('name_aa')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Car::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropColumn(['name_aa']);
            }
        );
    }
};
