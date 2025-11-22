<?php


use App\Models\Inventories\Inventory;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Orders\BS\TypeOfWorkInventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(TypeOfWorkInventory::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('type_of_work_id')
                ->nullable()
                ->references('id')->on(TypeOfWork::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('inventory_id')
                ->nullable()
                ->references('id')->on(Inventory::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->double('quantity');
            $table->double('price')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TypeOfWorkInventory::TABLE);
    }
};
