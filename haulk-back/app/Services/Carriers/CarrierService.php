<?php


namespace App\Services\Carriers;


use App\Models\Saas\Company\Company;
use App\Notifications\Carrier\DestroyCompany;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class CarrierService
{

    /**
     * @param Company $company
     * @throws Throwable
     */
    public function sendDestroyNote(Company $company): void
    {
        try {
            DB::beginTransaction();

            $company->crm_confirm_token = hash('sha256', Str::random(60));
            $company->crm_decline_token = hash('sha256', Str::random(60));
            $company->crm_date_token_create = Carbon::now()->toDateTimeString();

            $company->saveOrFail();

            $user = $company->getPaymentContactData();

            Notification::route('mail', $user['email'])->notify(new DestroyCompany($company));

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);

            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @param Company $company
     * @throws Throwable
     */
    public function declineDestroy(Company $company): void
    {
        try {
            DB::beginTransaction();

            $company->crm_confirm_token = null;
            $company->crm_decline_token = null;
            $company->crm_date_token_create = null;

            $company->save();

            DB::commit();

        } catch (Exception $e) {
            Log::error($e);

            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @param Company $company
     * @throws Throwable
     */
    public function confirmDestroy(Company $company): void
    {
        try {
            DB::beginTransaction();

            $company->crm_confirm_token = null;
            $company->crm_decline_token = null;
            $company->crm_date_token_create = null;

            $company->save();

            DB::commit();

            Artisan::queue('companies:delete', [
                'company_id' => $company->id,
            ]);

        } catch (Exception $e) {
            Log::error($e);

            DB::rollBack();

            throw $e;
        }
    }
}
