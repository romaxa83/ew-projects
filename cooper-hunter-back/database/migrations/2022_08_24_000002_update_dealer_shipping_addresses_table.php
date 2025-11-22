<?php

use App\Models\Locations\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dealer_shipping_addresses',
            static function (Blueprint $table) {
                $table->unsignedInteger('country_id')->after('dealer_id');
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
        Schema::table('dealer_shipping_addresses',
            static function (Blueprint $table) {
                $table->dropForeign(['country_id']);
                $table->dropColumn(['country_id']);
            }
        );
    }
};

