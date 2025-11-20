<?php

use App\Models\Security\IpAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(IpAccess::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->ipAddress('address');
                $table->string('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(IpAccess::TABLE);
    }
};
