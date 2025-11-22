<?php

use App\Models\Commercial\CommercialProject;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(WarrantyRegistration::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('commercial_project_id')
                    ->after('system_id')->nullable();
                $table->foreign('commercial_project_id')
                    ->references('id')
                    ->on(CommercialProject::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );

        Schema::table(WarrantyRegistrationDeleted::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('commercial_project_id')
                    ->after('system_id')->nullable();
                $table->foreign('commercial_project_id')
                    ->references('id')
                    ->on(CommercialProject::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::table(WarrantyRegistration::TABLE,
            static function (Blueprint $table) {
                $table->dropForeign(['commercial_project_id']);
                $table->dropColumn('commercial_project_id');
            }
        );

        Schema::table(WarrantyRegistrationDeleted::TABLE,
            static function (Blueprint $table) {
                $table->dropForeign(['commercial_project_id']);
                $table->dropColumn('commercial_project_id');
            }
        );
    }
};


