<?php

use App\Models\Partners\Partner;
use App\Models\Truck\Truck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn(Truck::TABLE, 'partner_id')) {
            Schema::table(Truck::TABLE, function (Blueprint $table) {
                $table->integer('partner_id')
                    ->nullable()
                    ->references('id')
                    ->on(Partner::TABLE)
                    ->onDelete('cascade')
                ;
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn(Truck::TABLE, 'partner_id')) {
                Schema::table(Truck::TABLE, function (Blueprint $table) {
                    $table->dropColumn('partner_id');
                });
        }
    }
};
