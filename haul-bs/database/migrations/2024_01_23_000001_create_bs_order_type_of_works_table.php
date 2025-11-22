<?php


use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWork;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(TypeOfWork::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->nullable()
                ->references('id')
                ->on(Order::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('duration');
            $table->double('hourly_rate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TypeOfWork::TABLE);
    }
};
