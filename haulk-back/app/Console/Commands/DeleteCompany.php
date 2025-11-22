<?php

namespace App\Console\Commands;

use App\Models\Billing\ActiveDriverHistory;
use App\Models\Contacts\Contact;
use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\Library\LibraryDocument;
use App\Models\Lists\BonusType;
use App\Models\Lists\ExpenseType;
use App\Models\News\News;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use App\Models\Payrolls\Payroll;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\QuestionAnswer\QuestionAnswer;
use App\Models\Reports\DriverTripReport;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:delete {company_id?} {--backoffice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete company and all it\'s data';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->info('Starting..');

        $backoffice = $this->option('backoffice');
        if ($backoffice) {
            $companies = Company::filter(['deletion' => Carbon::now()->toDateString()])->get();
            if ($companies === null) {
                $this->info('Nothing to deletion.');
                return 0;
            }
        } else {
            $companyId = $this->argument('company_id');

            if (!$companyId) {
                $this->error('Please provide company_id');
            }

            $company= Company::find($companyId);

            if (!$company) {
                $this->error('Company not found');
            }

            $companies = collect([$company]);
        }

        foreach ($companies as $company) {
            $this->info('Starting delete company ' . $company->name);

            try {
                DB::beginTransaction();

                $company->deactivate();
                $this->deleteUploadedFiles();
                $this->deleteOtherModules($company);
                $this->deletePayrolls($company);
                $this->deleteReports($company);
                $this->deleteOrders($company);
                $this->deleteVehicles($company);
                $this->deleteUsers($company);
                $this->deleteLists($company);
                $this->deleteSubscription($company);
                $this->deleteGpsData($company);
                $this->deleteCompany($company);

                DB::commit();

                $this->info('Company ' . $company->name . ' is delete');
            } catch (Exception $e) {
                Log::error($e);

                DB::rollBack();

                $this->error('Error delete company ' . $company->name . '. Error: ' . $e->getMessage());
            }
        }

        return 0;
    }

    public function deleteUploadedFiles(): void
    {
        // TODO: delete files uploaded with file browser
    }

    public function deleteCompany(Company $company): void
    {
        try {
            DB::beginTransaction();

            $company->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteGpsData(Company $company): void
    {
        $this->info("Delete Gps data");

        $res = Alert::query()->where('company_id', $company->id)->delete();

        $this->info("Delete GPS Alert [$res]");

        $res = History::query()->where('company_id', $company->id)->delete();

        $this->info("Delete GPS History [$res]");
    }

    public function deleteOrders(Company $company): void
    {
        try {
            DB::beginTransaction();

            $inspectionIDs = Vehicle::select(
                [
                    'pickup_inspection_id',
                    'delivery_inspection_id',
                ]
            )->whereHas(
                'order',
                function ($q) use ($company) {
                    $q->withoutGlobalScopes(
                    )->where(
                        'carrier_id',
                        $company->id
                    );
                }
            );

            if ($inspectionIDs) {
                Inspection::whereIn(
                    'id',
                    $inspectionIDs->pluck('pickup_inspection_id')
                )->delete();

                Inspection::whereIn(
                    'id',
                    $inspectionIDs->pluck('delivery_inspection_id')
                )->delete();
            }

            Order::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->forceDelete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteUsers(Company $company): void
    {
        try {
            DB::beginTransaction();

            $models = User::query()
                ->where('carrier_id', $company->id)
                ->get();

            foreach ($models as $model){
                /** @var $model User */
                $model->driverTrucksHistory()->delete();
                $model->driverTrailersHistory()->delete();
                $model->ownerTrucksHistory()->delete();
                $model->ownerTrailersHistory()->delete();

                DeviceRequest::query()->where('user_id', $model->id)->delete();

                $model->forceDelete();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteVehicles(Company $company): void
    {
        Trailer::query()->where('carrier_id', $company->id)->delete();
        Truck::query()->where('carrier_id', $company->id)->delete();
    }

    public function deleteLists(Company $company): void
    {
        try {
            DB::beginTransaction();

            ExpenseType::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->delete();

            BonusType::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteReports(Company $company): void
    {
        try {
            DB::beginTransaction();

            DriverTripReport::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deletePayrolls(Company $company): void
    {
        try {
            DB::beginTransaction();

            Payroll::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteSubscription(Company $company): void
    {
        try {
            DB::beginTransaction();

            if ($company->hasPaymentContact()) {
                $company->paymentContact->delete();
            }

            if ($company->hasPaymentMethod()) {
                $company->paymentMethod->delete();
            }

            $company->subscription->delete();

            ActiveDriverHistory::where('carrier_id', $company->id)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteOtherModules(Company $company): void
    {
        try {
            DB::beginTransaction();

            PushNotificationTask::whereIn(
                'user_id',
                $company->users()->pluck('id')
            )->delete();

            LibraryDocument::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->delete();

            QuestionAnswer::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->delete();

            News::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->delete();

            Contact::withoutGlobalScopes(
            )->where(
                'carrier_id',
                $company->id
            )->forceDelete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
