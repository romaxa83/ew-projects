<?php

use App\Models\Commercial\Commissioning\ProjectProtocol;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(ProjectProtocol::TABLE,
            static function (Blueprint $table) {
                $table->integer('sort')->after('status')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(ProjectProtocol::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('sort');
            }
        );
    }
};


