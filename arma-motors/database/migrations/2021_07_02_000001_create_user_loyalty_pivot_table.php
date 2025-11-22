<?php

use App\Models\Catalogs\Calc\Work;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLoyaltyPivotTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_car_loyalty_pivot', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('loyalty_id');
            $table->foreign('loyalty_id')
                ->references('id')
                ->on('loyalties')
                ->onDelete('cascade');
            $table->unsignedBigInteger('car_id');
            $table->foreign('car_id')
                ->references('id')
                ->on('user_cars')
                ->onDelete('cascade');

            $table->boolean('active')->default(true);
//            $table->primary(['user_id', 'loyalty_id', 'car_id'], 'pk-uclp_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_car_loyalty_pivot');
    }
}

