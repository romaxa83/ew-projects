<?php

use App\Enums\Companies\CompanyStatus;
use App\Models\Locations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Companies;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Companies\Company::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->uuid('guid')->nullable()->unique();

                $table->string('status', 15)->default(CompanyStatus::DRAFT);
                $table->string('type', 30);

                $table->unsignedBigInteger('corporation_id')->nullable();
                $table->foreign('corporation_id')
                    ->references('id')
                    ->on(Companies\Corporation::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('code', 36)
                    ->nullable()->unique('companies_code_unique');
                $table->json('terms')->nullable();

                $table->string('business_name');
                $table->string('email')->unique();
                $table->string('phone', 24)->nullable()->unique();
                $table->string('fax', 24)->nullable()->unique();

                $table->unsignedInteger('country_id');
                $table->foreign('country_id')
                    ->references('id')
                    ->on(Locations\Country::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->unsignedInteger('state_id');
                $table->foreign('state_id')
                    ->references('id')
                    ->on(Locations\State::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->string('city');
                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('po_box');
                $table->string('zip');

                $table->string('taxpayer_id')->nullable()->unique();
                $table->string('tax')->nullable();
                $table->json('websites')->nullable();
                $table->json('marketplaces')->nullable();
                $table->json('trade_names')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Companies\Company::TABLE);
    }
};
