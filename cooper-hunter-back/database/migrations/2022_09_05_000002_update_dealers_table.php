<?php

use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Dealer::TABLE,
            static function (Blueprint $table) {

                $table->unsignedBigInteger('company_id')->after('guid');
                $table->foreign('company_id')
                    ->references('id')
                    ->on(Company::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->boolean('is_main')->default(false);

                $table->dropForeign(['country_id']);
                $table->dropForeign(['state_id']);
                $table->dropColumn([
                    'status',
                    'type',
                    'code',
                    'terms',
                    'business_name',
                    'contact_email',
                    'country_id',
                    'state_id',
                    'city',
                    'address_line_1',
                    'address_line_2',
                    'po_box',
                    'zip',
                    'fax',
                    'taxpayer_id',
                    'tax',
                    'sites',
                    'links',
                    'trades',
                    'phone_verified_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Dealer::TABLE, function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'company_id',
                'is_main',
            ]);

            $table->string('status', 15)->default('draft');
            $table->string('type', 30);
            $table->string('code', 36)
                ->nullable()->unique('dealers_code_unique');
            $table->string('terms', 1000)->nullable();
            $table->string('business_name');
            $table->string('contact_email')->unique();
            $table->unsignedInteger('country_id');
            $table->foreign('country_id')
                ->references('id')
                ->on(Country::TABLE)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedInteger('state_id');
            $table->foreign('state_id')
                ->references('id')
                ->on(State::TABLE)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('city');
            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('po_box');
            $table->string('zip');
            $table->string('fax')->unique();
            $table->string('taxpayer_id')->nullable()->unique();
            $table->string('tax')->nullable();
            $table->json('sites')->nullable();
            $table->json('links')->nullable();
            $table->json('trades')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
        });
    }
};
