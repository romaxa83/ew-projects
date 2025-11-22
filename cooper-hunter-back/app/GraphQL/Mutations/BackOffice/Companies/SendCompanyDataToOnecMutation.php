<?php

namespace App\GraphQL\Mutations\BackOffice\Companies;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Models\Request\Request;
use App\Permissions\Companies\CompanySendDataToOnecPermission;
use App\Repositories\Companies\CompanyRepository;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SendCompanyDataToOnecMutation extends BaseMutation
{
    public const NAME = 'companySendDataToOnec';
    public const PERMISSION = CompanySendDataToOnecPermission::KEY;

    public function __construct(
        protected RequestService $serviceRequest,
        protected CompanyRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Company::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity
    {
        try {
            /** @var $model Company */
            $model = $this->repo->getBy('id', $args['id']);

            if($model->guid){
                throw new TranslatedException(__('messages.company.send_data_to_onec.has guid'), 502);
            }

            $this->serviceRequest->createCompany($model);

            if($err = $this->getError($model)){
                throw new \Exception($err);
            }

            return ResponseMessageEntity::success(__('messages.company.send_data_to_onec.message'));
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }

    public function getError(Company $model): ?string
    {
        $req = Request::query()
            ->where('command', 'CreateCompany')
            ->where('status', 'error')
            ->get()->filter(function ($item) use ($model) {
                if($item->send_data['id'] === $model->id){
                    return $item;
                }
            });

        if($req->isNotEmpty()){
            return (string)$req->last()->response_data;
        }

        return null;
    }
}
