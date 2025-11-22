<?php

use App\Models\Orders\Categories\OrderCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            OrderCategory::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(OrderCategory::DEFAULT_SORT);
                $table->boolean('active')->default(OrderCategory::DEFAULT_ACTIVE);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(OrderCategory::TABLE);
    }
};
