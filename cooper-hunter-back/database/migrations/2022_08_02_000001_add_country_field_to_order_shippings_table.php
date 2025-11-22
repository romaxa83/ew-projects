<?php

use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\OrderShipping;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(OrderShipping::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('country_id')
                    ->after('phone')->nullable();
                $table->foreign('country_id')
                    ->references('id')
                    ->on(Country::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('state_id')
                    ->after('country_id')->nullable();
                $table->foreign('state_id')
                    ->references('id')
                    ->on(State::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('state')->nullable()->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(OrderShipping::TABLE, function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropColumn(['state_id']);
            $table->string('state')->change();
        });
    }
};





