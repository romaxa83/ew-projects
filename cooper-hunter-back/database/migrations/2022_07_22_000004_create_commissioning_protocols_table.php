<?php

use App\Models\Commercial\Commissioning\Protocol;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Protocol::TABLE,
            static function (Blueprint $table)
            {
                $table->increments('id');
                $table->string('type', 20);
                $table->integer('sort')->default(0);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Protocol::TABLE);
    }
};

