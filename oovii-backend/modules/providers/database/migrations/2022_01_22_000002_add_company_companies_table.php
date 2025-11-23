<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Providers\Models\Provider;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Provider::TABLE,
            static function (Blueprint $table) {
                $table->string('company')->after('name')->nullable();

                $table->unsignedBigInteger('admin_id')->nullable();
                $table->foreign('admin_id')
                    ->references('id')
                    ->on('administrators')
                    ->onDelete('cascade');

                $table->dropForeign('providers_company_id_foreign');
                $table->dropColumn('company_id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Provider::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable();
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');

                $table->dropColumn("company");

                $table->dropForeign('providers_admin_id_foreign');
                $table->dropColumn('admin_id');
            }
        );
    }
};
