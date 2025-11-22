<?php

use App\Models\Dealers\Dealer;
use App\Models\Locations\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dealer_contact_accounts',
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('dealer_id');
                $table->foreign('dealer_id')
                    ->references('id')
                    ->on(Dealer::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('name');
                $table->string('phone')->unique();
                $table->string('email')->unique();

                $table->unsignedInteger('state_id');
                $table->foreign('state_id')
                    ->references('id')
                    ->on(State::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('city');
                $table->string('address');
                $table->string('po_box');
                $table->string('zip');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_contact_accounts');
    }
};
