<?php

use App\Enums\About\ForMemberPageEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'for_member_pages',
            static function (Blueprint $table) {
                $table->enum('for_member_type', ForMemberPageEnum::getValues())->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'for_member_pages',
            static function (Blueprint $table) {
            }
        );
    }
};
