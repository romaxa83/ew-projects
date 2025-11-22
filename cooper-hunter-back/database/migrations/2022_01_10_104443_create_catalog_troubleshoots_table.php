<?php

use App\Models\Catalog\Troubleshoots\Troubleshoot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_troubleshoots',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(Troubleshoot::DEFAULT_SORT);
                $table->boolean('active')->default(Troubleshoot::DEFAULT_ACTIVE);
                $table->string('name', 300);

                $table->unsignedBigInteger('group_id');
                $table->foreign('group_id')
                    ->on('catalog_troubleshoot_groups')
                    ->references('id')
                    ->onDelete('cascade')
                    ->cascadeOnUpdate();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_troubleshoots');
    }
};


