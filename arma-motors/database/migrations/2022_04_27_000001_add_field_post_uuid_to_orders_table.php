<?php

use App\Models\Agreement\Agreement;
use App\Models\Order\Additions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Additions::TABLE_NAME,
            static function (Blueprint $table) {
                $table->string('post_uuid')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Additions::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropColumn(['post_uuid']);
            }
        );
    }
};

