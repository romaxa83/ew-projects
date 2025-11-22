<?php

use App\Models\GlobalSettings\GlobalSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(GlobalSetting::TABLE,
            static function (Blueprint $table) {
                $table->string('company_site')->nullable();
                $table->string('company_title')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(GlobalSetting::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('company_site');
                $table->dropColumn('company_title');
            }
        );
    }
};
