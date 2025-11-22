<?php

use App\Models\Companies\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Company::TABLE,
            static function (Blueprint $table) {
                $table->string('terms')->nullable()->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Company::TABLE,
            static function (Blueprint $table) {
                $table->json('terms')->change();
            }
        );
    }
};
