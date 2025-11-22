<?php

use App\Models\Catalog\Products\Product;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(State::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('country_id')->nullable();
                $table->foreign('country_id')
                    ->references('id')
                    ->on(Country::TABLE)
                    ->index('idx_state_country_id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE, function (Blueprint $table) {
            $table->dropForeign('idx_state_country_id');
            $table->dropColumn(['country_id']);
        });
    }
};

