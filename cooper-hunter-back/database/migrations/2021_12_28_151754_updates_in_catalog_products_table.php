<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_products',
            static function (Blueprint $table) {
                $table->after(
                    'active',
                    static function (Blueprint $table) {
                        $table->string('vendor_code')->nullable()->unique();
                        $table->string('model')->nullable()->unique();
                    }
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_products',
            static function (Blueprint $table) {
                $table->dropUnique(['vendor_code']);
                $table->dropUnique(['model']);

                $table->dropColumn('vendor_code', 'model');
            }
        );
    }
};
