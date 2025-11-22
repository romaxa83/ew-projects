<?php

use App\Models\Commercial\CommercialProject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CommercialProject::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('state');
            }
        );
    }

    public function down(): void
    {
        Schema::table(CommercialProject::TABLE,
            static function (Blueprint $table) {
                $table->string('state');
            }
        );
    }
};


