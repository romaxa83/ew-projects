<?php

use App\Models\Fueling\FuelCard;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelingHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fueling_history', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->integer('total')->default(0);
            $table->integer('count_errors')->default(0);
            $table->integer('counts_success')->default(0);
            $table->integer('progress')->default(0);
            $table->string('path_file')->nullable();
            $table->string('original_name')->nullable();
            $table->string('status')->nullable();
            $table->string('provider')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->references('id')
                ->on(User::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->index('broker_id');
            $table->index('carrier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fueling_history');
    }
}
