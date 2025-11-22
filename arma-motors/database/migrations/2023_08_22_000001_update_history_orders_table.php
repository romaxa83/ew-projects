<?php

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\History\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE,
            static function (Blueprint $table) {
                $table->string('recommendations', 3000)
                    ->nullable()
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Order::TABLE,
            static function (Blueprint $table) {
                $table->string('recommendations')
                    ->nullable()
                    ->change();
            }
        );
    }
};



