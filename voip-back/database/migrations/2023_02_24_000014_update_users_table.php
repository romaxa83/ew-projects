<?php

use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(User::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('full_name_normalized');
            });
    }

    public function down(): void
    {
        Schema::table(User::TABLE,
            function (Blueprint $table) {
                $table->string('full_name_normalized', 150)
                    ->storedAs("regexp_replace(last_name || ' ' || first_name, '[^A-Za-zА-Яа-я0-9 ]/i', '')")
                    ->index();
            });
    }
};
