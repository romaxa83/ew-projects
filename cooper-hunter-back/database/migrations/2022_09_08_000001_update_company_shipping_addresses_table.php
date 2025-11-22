<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Companies;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Companies\ShippingAddress::TABLE,
            static function (Blueprint $table) {
                $table->dropUnique(['phone']);
                $table->dropUnique(['fax']);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Companies\ShippingAddress::TABLE,
            static function (Blueprint $table) {
                $table->string('phone')
                    ->unique()
                    ->change();
                $table->string('fax')
                    ->unique()
                    ->change();
            }
        );
    }
};



