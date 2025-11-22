<?php

use App\Models\Inventories\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Category::TABLE, function (Blueprint $table) {
            $table->boolean('display_menu')
                ->after('position')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Category::TABLE, function (Blueprint $table) {
            $table->dropColumn('display_menu');
        });
    }
};
