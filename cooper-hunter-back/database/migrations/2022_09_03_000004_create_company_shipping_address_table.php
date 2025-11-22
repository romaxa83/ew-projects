<?php

use App\Models\Companies;
use App\Models\Locations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Companies\ShippingAddress::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('company_id');
                $table->foreign('company_id')
                    ->references('id')
                    ->on(Companies\Company::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('phone')->unique();
                $table->string('fax')->nullable()->unique();

                $table->unsignedInteger('country_id');
                $table->foreign('country_id')
                    ->references('id')
                    ->on(Locations\Country::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->unsignedInteger('state_id');
                $table->foreign('state_id')
                    ->references('id')
                    ->on(Locations\State::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->string('city');
                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('po_box');
                $table->string('zip');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Companies\ShippingAddress::TABLE,);
    }
};
