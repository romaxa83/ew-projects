<?php

use App\Models\Catalog\Manuals\ManualGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'manual_groups',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(ManualGroup::DEFAULT_SORT);
                $table->boolean('active')->default(ManualGroup::DEFAULT_ACTIVE);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_groups');
    }
};
