<?php

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Troubleshoots\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_troubleshoot_groups',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(Group::DEFAULT_SORT);
                $table->boolean('active')->default(Group::DEFAULT_ACTIVE);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_troubleshoot_groups');
    }
};

