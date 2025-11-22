<?php

use App\Models\About\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'pages',
            static function (Blueprint $table)
            {
                $table->id();
                $table->boolean('active')
                    ->default(Page::DEFAULT_ACTIVE);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
