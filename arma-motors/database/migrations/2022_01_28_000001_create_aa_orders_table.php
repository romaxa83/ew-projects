<?php

use App\Models\AA\AAOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(AAOrder::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('order_uuid')->unique()->nullable();
                $table->string('user_uuid')->nullable();
                $table->string('car_uuid')->nullable();
                $table->string('service_alias')->nullable();
                $table->string('sub_service_alias')->nullable();
                $table->string('dealership_alias')->nullable();

                $table->string('post_uuid')->nullable();
                $table->foreign('post_uuid')
                    ->references('uuid')
                    ->on('aa_posts');

                $table->timestamp('start_date');
                $table->timestamp('end_date');
                $table->string('comment', 1000)->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(AAOrder::TABLE);
    }
};

