<?php

use App\Models\Commercial\Tax;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Tax::TABLE,
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('guid', 36)->unique();
                $table->string('name');
                $table->unsignedInteger('value');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Tax::TABLE);
    }
};

