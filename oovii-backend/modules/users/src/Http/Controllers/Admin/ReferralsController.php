<?php

namespace WezomCms\Users\Http\Controllers\Admin;

use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\Number;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Users\Exports\ReferralExport;
use WezomCms\Users\Http\Requests\Admin\UpdateInviterRequest;
use WezomCms\Users\Models\Inviter;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\UserRepository;

class ReferralsController extends AbstractCRUDController
{
    use SettingControllerTrait;
    use AjaxResponseStatusTrait;

    protected $model = Inviter::class;

    protected $view = 'cms-users::admin.referrals';

    protected $routeName = 'admin.referrals';

    protected $updateRequest = UpdateInviterRequest::class;

    protected ?string $exportUrl = 'admin.referrals.export';

    public function __construct(
        protected UserRepository $repo
    )
    {
        parent::__construct();
    }

    protected function title(): string
    {
        return __('cms-users::admin.Referrals');
    }

    protected function abilityPrefix(): ?string
    {
        return 'referrals';
    }

    protected function selectionIndexResult($query, Request $request): void
    {
        $query
            ->whereHas('referrals')
            ->withCount('referrals');
    }

    protected function editViewData(Inviter $obj, array $viewData): array
    {
        $obj->referrals
            ->loadMissing(['referralBonusHistory']);

        return [
            'referrals' => $obj->referrals,
        ];
    }

    protected function fillUpdateData($obj, FormRequest $request): array
    {
        /** @var Inviter $obj */
        $bonusDiff = (int) $request->get('bonus') - $obj->bonus;

        if ($bonusDiff !== 0) {
            $obj->createCorrectionTransaction($bonusDiff);
        }

        return [];
    }

    /**
     * @throws Exception
     */
    protected function settings(): array
    {
        return [
            Number::make(RenderSettings::siteTab())
                ->default(config('cms.users.users.referrals.bonus_limit', 10))
                ->setKey('referral_bonus_limit')
                ->setName(__('cms-users::admin.referrals.Bonus number'))
                ->setRules('required|integer|min:0')
                ->setSort(1),
            AdminLimit::make(),
        ];
    }

    public function detach(User $referral): JsonResponse
    {
        $referral->ref_id = null;
        $referral->save();

        return $this->success();
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new ReferralExport($request, $this->repo), 'referrals.xlsx');
        } catch (Exception $e){
            report($e);
            flash($e->getMessage())->error();

            return redirect()->route($this->makeRouteName('index'));
        }
    }
}
