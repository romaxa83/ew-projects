<?php

use App\Models\Locations\Country;
use App\Models\Technicians\Technician;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Technician::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('country_id')->nullable();
                $table->foreign('country_id')
                    ->references('id')
                    ->on(Country::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Technician::TABLE, function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['country_id']);
        });
    }
};



