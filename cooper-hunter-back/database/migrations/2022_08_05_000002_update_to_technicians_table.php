<?php

use App\Models\Technicians\Technician;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Technician::TABLE,
            static function (Blueprint $table) {
                $table->boolean('is_commercial_certification')
                    ->after('is_verified')
                    ->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Technician::TABLE, function (Blueprint $table) {
            $table->dropColumn('is_commercial_certification');
        });
    }
};





