<?php

use App\Models\Orders\OrderShipping;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(OrderShipping::TABLE,
            static function (Blueprint $table) {
                $table->string('country', 50)
                    ->after('state')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(OrderShipping::TABLE, function (Blueprint $table) {
            $table->dropForeign(['country']);
        });
    }
};




