<?php

use App\Models\Admins\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Admin::TABLE,
            function (Blueprint $table) {
                $table->timestamp('email_verified_at')
                    ->after('email')->nullable();
                $table->string('email_verification_code', 16)
                    ->after('email_verified_at')
                    ->nullable();
            });
    }

    public function down(): void
    {
        Schema::table(Admin::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
                $table->dropColumn('email_verification_code');
            });
    }
};


