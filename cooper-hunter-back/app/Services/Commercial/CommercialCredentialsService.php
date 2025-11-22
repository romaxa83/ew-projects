<?php

namespace App\Services\Commercial;

use Adldap\AdldapException;
use App\Contracts\Members\HasCommercialProjects;
use App\Contracts\Roles\HasGuardUser;
use App\Dto\Commercial\CommercialCredentialsDto;
use App\Entities\Messages\ResponseMessageEntity;
use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use App\Enums\Formats\DatetimeEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Events\Commercial\RDPCredentialsGeneratedEvent;
use App\Exceptions\Commercial\CredentialsGenerationException;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CredentialsRequest;
use App\Models\Commercial\RDPAccount;
use App\Models\Localization\Translate;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CommercialCredentialsService
{
    public function __construct(private RDPService $rdp)
    {}

    public function requestCredentials(
        HasCommercialProjects $user,
        CommercialCredentialsDto $dto
    ): ResponseMessageEntity {
        $project = CommercialProject::findOrFail($dto->getProjectId());

        try {
            $this->assertCanCreateCredentials($project);
        } catch (CredentialsGenerationException $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }

        $this->createRequest($user, $project, $dto);

        return ResponseMessageEntity::success(
            _t(
                Translate::SITE_PLACE,
                'commercial_requests__request_success'
            )
        );
    }

    /** @throws CredentialsGenerationException */
    protected function assertCanCreateCredentials(CommercialProject $project): void
    {
        $throw = static fn(string $message = '') => throw  new CredentialsGenerationException($message);

        if (!$project->code || !$project->status->isPending() || $project->estimate_end_date->lte(now())) {
            $throw(__('Wrong project provided'));
        }

        if ($project->member->hasValidRdpAccount()) {
            $throw(__('Credentials already exists'));
        }

        if ($project->member->credentialRequests()->hasValidPendingRequest()->exists()) {
            $throw(
                _t(
                    Translate::SITE_PLACE,
                    'commercial_requests__request_exists'
                )
            );
        }
    }

    private function createRequest(
        HasCommercialProjects $user,
        CommercialProject $project,
        CommercialCredentialsDto $dto
    ): void {
        $request = new CredentialsRequest();

        $request->member_type = $user->getMorphType();
        $request->member_id = $user->getId();

        $request->company_name = $dto->getCompanyName();
        $request->company_phone = $dto->getCompanyPhone();
        $request->company_email = $dto->getCompanyEmail();
        $request->comment = $dto->getComment();

        $request->commercialProject()->associate($project);

        $request->save();
    }

    public function approve(CredentialsRequest $request, string $endDate): bool
    {
        $request->status = CommercialCredentialsStatusEnum::APPROVED();
        $request->processed_at = now();
        $request->end_date = Carbon::createFromFormat(DatetimeEnum::DATE, $endDate)->endOfDay();

        $this->generateCredentials($request);

        return $request->save();
    }

    private function generateCredentials(CredentialsRequest $request): void
    {
        $rdpAccount = $this->getRDPAccount($request);
        $rdpAccount->start_date = now();
        $rdpAccount->end_date = $request->end_date;
        $rdpAccount->active = true;
        $rdpAccount->save();

        $this->finishSavingRDPAccount($rdpAccount);
    }

    public function getRDPAccount(CredentialsRequest $request): RDPAccount
    {
        /** @var RDPAccount $account */
        $account = $request->member->rdpAccount()->firstOrNew();

        if (!$account->exists) {
            $account->login = $account->getLoginString();
            $account->active = true;
            $account->created_on_ad = false;
            $account->password = generatePassword();

            if (RDPAccount::query()->where('login', $account->login)->exists()) {
                $account->login .= '1';
            }
        }

        return $account;
    }

    private function finishSavingRDPAccount(RDPAccount $rdpAccount): void
    {
        try {
            $this->rdp->createOrUpdateUser($rdpAccount);

            event(new RDPCredentialsGeneratedEvent($rdpAccount));
        } catch (AdldapException $e) {
            logger($e);
        }
    }

    public function deny(CredentialsRequest $request): bool
    {
        $request->status = CommercialCredentialsStatusEnum::DENIED();
        $request->processed_at = now();

        $this->revokeCredentials($request);

        return $request->save();
    }

    private function revokeCredentials(CredentialsRequest $request): void
    {
        //todo отозвать креды если есть
    }

    public function getCounterData(HasGuardUser $user): Collection
    {
        $total = CredentialsRequest::query()
            ->selectRaw(
                "
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS new,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS approved,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS denied,
            COUNT(*) AS total
        ",
                [
                    CommercialCredentialsStatusEnum::NEW,
                    CommercialCredentialsStatusEnum::APPROVED,
                    CommercialCredentialsStatusEnum::DENIED,
                ]
            )
            ->first();

        return collect(
            [
                'new' => $total->new ?? 0,
                'approved' => $total->approved ?? 0,
                'denied' => $total->denied ?? 0,
                'total' => $total->total ?? 0,
            ]
        );
    }
}
