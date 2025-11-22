<?php

use App\Models\Orders\Dealer\SerialNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn(SerialNumber::TABLE, 'id')) {
            Schema::table(
                SerialNumber::TABLE,
                static function (Blueprint $table) {
                    $table->bigIncrements('id');
                }
            );
        }
    }

    public function down(): void
    {
    }
};

