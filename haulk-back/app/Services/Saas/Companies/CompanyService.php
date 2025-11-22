<?php


namespace App\Services\Saas\Companies;


use App\Models\Admins\Admin;
use App\Models\Saas\Company\Company;
use App\Models\Saas\Company\CompanySetting;
use App\Notifications\Saas\Companies\ConfirmDestroyCompany;
use App\Notifications\Saas\Companies\DestroyCompany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompanyService
{

    public function sendDestroyTokens(Company $company, Admin $user)
    {
        try {
            DB::beginTransaction();

            $company->saas_confirm_token = hash('sha256', Str::random(60));
            $company->saas_decline_token = hash('sha256', Str::random(60));
            $company->saas_date_token_create = Carbon::now()->toDateTimeString();

            $company->save();

            $user->notify(new ConfirmDestroyCompany($company));

            DB::commit();
        } catch (\Exception $e) {
            Log::error($e);

            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @param Company $company
     * @return bool
     * @throws \Throwable
     */
    public function declineDestroy(Company $company): bool
    {
        try {
            DB::beginTransaction();

            $company->saas_confirm_token = null;
            $company->saas_decline_token = null;
            $company->saas_date_token_create = null;

            $company->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            Log::error($e);

            DB::rollBack();
        }
        return false;
    }

    /**
     * @param Company $company
     * @return bool
     * @throws \Throwable
     */
    public function confirmDestroy(Company $company): bool
    {
        try {
            DB::beginTransaction();

            $company->saas_confirm_token = null;
            $company->saas_decline_token = null;
            $company->saas_date_token_create = null;
            $company->saas_date_delete = Carbon::now()->addMonth()->toDateString();

            $company->save();

            $superAdmin = $company->getSuperAdmin();

            if ($superAdmin !== null) {
                $superAdmin->notify(new DestroyCompany($company));
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            Log::error($e);

            DB::rollBack();
        }
        return false;
    }

    public function makeCompanySettingModel(Company $company): void
    {
        if ($company->setting !== null) {
            return;
        }

        $setting = new CompanySetting();
        $setting->company_id = $company->id;
        $setting->save();

        $company->refresh();

        $this->setFileBrowserPrefix($company);
    }

    public function setFileBrowserPrefix(Company $company): void
    {
        $setting = $company->setting;

        if ($setting->filebrowser_prefix) {
            return;
        }

        $setting->filebrowser_prefix = hash('sha256', Str::random(60));
        $setting->save();
    }

    public function delete(Company $company): void
    {
        if($company->active){
            throw new \Exception('Failed to delete active company');
        }

        Artisan::call('companies:delete', [
            'company_id' => $company->id,
        ]);
    }
}
