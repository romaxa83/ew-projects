<?php

use App\Models\Catalog\Manuals\ManualGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(ManualGroup::TABLE,
            static function (Blueprint $table) {
                $table->boolean('show_commercial_certified')
                    ->after('active')
                    ->default(false)
                ;
            }
        );
    }

    public function down(): void
    {
        Schema::table(ManualGroup::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('show_commercial_certified')
                ;
            }
        );
    }
};

