<?php

use App\Models\Companies;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Companies\ShippingAddress::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('email');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Companies\ShippingAddress::TABLE,
            static function (Blueprint $table) {
                $table->string('email')->nullable()->unique();
            }
        );
    }
};

