<?php

use App\Models\Orders\Bonus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {

    public function up(): void
    {
        Schema::table(
            Bonus::TABLE_NAME,
            static function (Blueprint $table): void {
                $table->index('order_id');
            }
        );
    }

    public function down()
    {
        Schema::table(
            Bonus::TABLE_NAME,
            static function (Blueprint $table): void {
                $table->dropIndex(['order_id']);
            }
        );
    }
};
