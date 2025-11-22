<?php

use App\Models\Orders\Dealer\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Item::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('total')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Item::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('total');
            }
        );
    }
};


