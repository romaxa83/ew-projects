<?php

use App\Models\Orders\Dealer\Dimensions;
use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Dimensions::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('packing_slip_id');
                $table->foreign('packing_slip_id')
                    ->on(PackingSlip::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->integer('pallet')->nullable();
                $table->integer('box_qty')->nullable();
                $table->string('type')->nullable();
                $table->float('weight')->nullable();
                $table->float('width')->nullable();
                $table->float('depth')->nullable();
                $table->float('height')->nullable();
                $table->integer('class_freight')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Dimensions::TABLE);
    }
};
