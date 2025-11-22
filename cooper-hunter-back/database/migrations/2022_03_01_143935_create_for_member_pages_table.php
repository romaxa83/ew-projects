<?php

use App\Enums\About\ForMemberPageEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'for_member_pages',
            static function (Blueprint $table) {
                $table->id();
                $table->enum('for_member_type', ForMemberPageEnum::getValues())->unique();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('for_member_pages');
    }
};
