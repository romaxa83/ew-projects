<?php

use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Warranty\Deleted\WarrantyAddressDeleted;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(WarrantyAddressDeleted::TABLE, function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('warranty_id');
            $table->foreign('warranty_id')
                ->references('id')
                ->on(WarrantyRegistrationDeleted::TABLE)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedInteger('country_id');
            $table->foreign('country_id')
                ->references('id')
                ->on(Country::TABLE)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedInteger('state_id');
            $table->foreign('state_id')
                ->references('id')
                ->on(State::TABLE)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('city');
            $table->string('street');
            $table->string('zip', 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(WarrantyAddressDeleted::TABLE);
    }
};
