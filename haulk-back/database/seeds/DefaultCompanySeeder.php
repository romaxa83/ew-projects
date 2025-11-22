<?php

use App\Models\Saas\Company\Company;
use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use App\Models\Users\User;
use App\Services\Saas\Companies\CompanyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DefaultCompanySeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        Model::reguard();

        $companyRegistrationData = config('haulk');

        if (Company::where('usdot', $companyRegistrationData['usdot'])->exists()) {
            return;
        }

        try {
            DB::beginTransaction();

            $companyRegistration = new CompanyRegistration($companyRegistrationData);
            $companyRegistration->password = bcrypt($companyRegistrationData['password']);

            $company = new Company($companyRegistrationData);
            $company->id = $companyRegistrationData['id'];
            $company->active = true;
            $company->save();

            if (User::where('email', $companyRegistration->email)->doesntExist()) {
                $company->createSuperAdmin($companyRegistration, $company->id);
            }

            $company->createSubscription(config('pricing.plans.haulk-exclusive.slug'));

            $company->billingInfo()->create($companyRegistrationData);
            $company->insuranceInfo()->create($companyRegistrationData);
            $company->notificationSettings()->create($companyRegistrationData);

            resolve(CompanyService::class)->makeCompanySettingModel($company);

            $paymentMethod = $company->paymentMethod()->create([]);
            $paymentMethod->payment_provider = 'test';
            $paymentMethod->payment_data = ['test'];
            $paymentMethod->save();

            DB::commit();
        } catch (Exception $exception) {
            Log::error($exception);

            DB::rollBack();
        }

        DB::statement("SELECT setval('" . Company::TABLE_NAME . "_id_seq', COALESCE((SELECT MAX(id)+1 FROM " . Company::TABLE_NAME . "), 1), false);");
    }
}
