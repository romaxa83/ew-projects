<?php

use App\Models\Dealers\Dealer;
use App\Models\Locations\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dealer_shipping_addresses',
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('dealer_id');
                $table->foreign('dealer_id')
                    ->references('id')
                    ->on(Dealer::TABLE)
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
                $table->string('po_box');
                $table->string('zip');
                $table->string('phone')->unique();
                $table->string('fax')->nullable()->unique();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_shipping_addresses');
    }
};
