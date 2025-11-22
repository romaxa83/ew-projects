<?php

use App\Models\Vehicles\Truck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Truck::TABLE_NAME, function (Blueprint $table) {
            $table->decimal('gvwr')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Truck::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('gvwr');
        });
    }
};
