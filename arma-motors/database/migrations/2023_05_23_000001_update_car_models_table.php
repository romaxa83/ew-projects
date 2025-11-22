<?php

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Brand::TABLE,
            static function (Blueprint $table) {
                $table->string('sys_type', 10)->default('old');
            }
        );

        Schema::table(Model::TABLE,
            static function (Blueprint $table) {
                $table->string('sys_type', 10)->default('old');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Brand::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('sys_type');
            }
        );

        Schema::table(Model::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('sys_type');
            }
        );
    }
};


