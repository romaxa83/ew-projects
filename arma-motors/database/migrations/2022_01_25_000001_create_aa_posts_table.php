<?php

use App\Models\AA\AAPost;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(AAPost::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('uuid')->unique();
                $table->string('name');
                $table->string('alias');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(AAPost::TABLE);
    }
};

