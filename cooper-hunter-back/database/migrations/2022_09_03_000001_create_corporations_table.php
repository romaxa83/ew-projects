<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Companies;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Companies\Corporation::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->uuid('guid')->nullable()->unique();
                $table->string('name', 4);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Companies\Corporation::TABLE);
    }
};
