<?php

use App\Foundations\Modules\Media\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Media::TABLE, function (Blueprint $table) {
            $table->integer('origin_id')->nullable();
            $table->boolean('is_main')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Media::TABLE, function (Blueprint $table) {
            $table->dropColumn('origin_id');
            $table->dropColumn('is_main');
        });
    }
};
