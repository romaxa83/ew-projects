<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Firebase\Models\Template;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Template::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->boolean('active')->default(true);
                $table->string('type')->unique();
                $table->json('vars');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Template::TABLE);
    }
};

