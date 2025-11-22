<?php

use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(ProjectProtocolQuestion::TABLE,
            static function (Blueprint $table) {
                $table->integer('sort')->after('answer_status')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(ProjectProtocolQuestion::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('sort');
            }
        );
    }
};



