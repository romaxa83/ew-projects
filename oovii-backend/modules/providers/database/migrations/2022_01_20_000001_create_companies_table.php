<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies',
            static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
