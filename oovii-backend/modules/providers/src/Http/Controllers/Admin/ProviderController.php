<?php

namespace WezomCms\Providers\Http\Controllers\Admin;

use AntistressStore\CdekSDK2\Entity\Responses\CitiesResponse;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Repositories\AdminRepository;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Orders\Services\SdekService;
use WezomCms\Providers\Exports\ProviderExport;
use WezomCms\Providers\Http\Requests\Admin\Provider;
use WezomCms\Providers\Models\Provider as ProviderModel;
use WezomCms\Providers\Repositories\ProviderRepository;
use WezomCms\Providers\Services\ProviderService;
use WezomCms\Providers\Types\ProviderStatus;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProviderController extends AbstractCRUDController
{
    use SettingControllerTrait;

    protected $model = ProviderModel::class;

    protected $view = 'cms-providers::admin.provider';

    protected $routeName = 'admin.providers';

    protected ?string $exportUrl = 'admin.providers.export';

    protected $createRequest = Provider\CreateRequest::class;
    protected $updateRequest = Provider\UpdateRequest::class;

    public function __construct(
        protected AdminRepository $adminRepository,
        protected ProviderRepository $repo,
        protected ProviderService $service,
        private SdekService $sdekService,
    )
    {
        parent::__construct();
    }

    protected function title(): string
    {
        return __('cms-providers::admin.provider.Providers');
    }

    protected function exportUrl(): ?string
    {
        return parent::exportUrl();
    }

    protected function selectionIndexResult($query, Request $request)
    {
        $query->withCount(['products'])->with(['adminProfile']);
    }

    protected function formData($obj, array $viewData): array
    {
        $regions = $this->sdekService->getRegionsForSelect();

        return [
            'statuses' => ProviderStatus::list(),
            'selectedStatus' => [ ProviderStatus::DRAFT ],
            'regions' => $regions,
            'cities' => [],
            'citiesList' => [],
        ];
    }

    protected function editViewData(ProviderModel $model, array $viewData): array
    {
        $regions = $this->sdekService->getRegionsForSelect();
        $regionCode = $model->region_code ?? $regions->keys()->first();
        $city = $this->sdekService->getCity($regionCode, $model->city_code);

        $citiesList = $this->sdekService->getCities($model->region_code)->map(function (CitiesResponse $item,$k){
            return [$k => $item->getCity()];
        });

        return [
            'statuses' => ProviderStatus::list(),
            'selectedStatus' => [$model->status],
            'regions' => $regions,
            'cities' => [ $model->city_code => $city ? $city->getCity() : null ],
            'citiesList' => $citiesList,
        ];
    }

    protected function fillStoreData($model, FormRequest $request): array
    {
        $model->password = bcrypt($request->get('password'));

        return $request->except('password');
    }

    protected function fillUpdateData($model, FormRequest $request): array
    {
        /** @var $model ProviderModel */
        if ($password = $request->get('password')) {
            $model->password = bcrypt($password);
        }

        if($model->isDraft() && ProviderStatus::MODERATED == $request->input("status")){
            if($this->adminRepository->existBy("email", $request->input("email"), $model->admin_id)){
                throw new Exception("Email не уникален [{$request->input('email')}]");
            }
        }

        return $request->except('password');
    }

    protected function afterSuccessfulSave(ProviderModel $model, FormRequest $request): void
    {
        if ($model->isModerate()) {
            $this->service->createAdminProfile($model);
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new ProviderExport($request, $this->repo), 'providers.xlsx');
        } catch (Exception $e){
            report($e);
            flash($e->getMessage())->error();

            return redirect()->route($this->makeRouteName('index'));
        }
    }

    protected function settings(): array
    {
        return [];
    }
}
