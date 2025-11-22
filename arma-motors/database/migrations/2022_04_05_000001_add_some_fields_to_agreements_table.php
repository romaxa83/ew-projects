<?php

use App\Models\Agreement\Agreement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Agreement::TABLE,
            static function (Blueprint $table) {
                $table->string('author')->nullable();
                $table->string('author_phone')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Agreement::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('author');
                $table->dropColumn('author_phone');
            }
        );
    }
};
