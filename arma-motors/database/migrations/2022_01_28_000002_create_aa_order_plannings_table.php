<?php

use App\Models\AA\AAOrder;
use App\Models\AA\AAOrderPlanning;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(AAOrderPlanning::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('aa_order_id');
                $table->foreign('aa_order_id')
                    ->references('id')
                    ->on(AAOrder::TABLE)
                    ->onDelete('cascade');

                $table->timestamp('start_date');
                $table->timestamp('end_date');
                $table->string('post_uuid')->nullable();
                $table->foreign('post_uuid')
                    ->references('uuid')
                    ->on('aa_posts');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(AAOrderPlanning::TABLE);
    }
};
