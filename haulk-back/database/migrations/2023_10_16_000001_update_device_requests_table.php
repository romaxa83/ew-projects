<?php

use App\Enums\Saas\GPS\Request\DeviceRequestSource;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(DeviceRequest::TABLE_NAME, function (Blueprint $table) {
            $table->string('source', 25)
                ->default(DeviceRequestSource::CRM);
        });
    }

    public function down(): void
    {
        Schema::table(Company::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
