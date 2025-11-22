<?php

use App\Models\Catalog\Pdf\Pdf;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Pdf::TABLE,
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('path')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Pdf::TABLE);
    }
};
