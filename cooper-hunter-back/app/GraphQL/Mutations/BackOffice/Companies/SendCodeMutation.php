<?php

namespace App\GraphQL\Mutations\BackOffice\Companies;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Permissions\Companies\CompanySendCodePermission;
use App\Repositories\Companies\CompanyRepository;
use App\Services\Companies\CompanyService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SendCodeMutation extends BaseMutation
{
    public const NAME = 'companySendCode';
    public const PERMISSION = CompanySendCodePermission::KEY;

    public function __construct(
        protected CompanyService $service,
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
            if(!$model->code){
                throw new TranslatedException(__('messages.company.send_code.has no code'), 502);
            }

            $this->service->sendCode($model);

            return ResponseMessageEntity::success(__('messages.company.send_code.message'));
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}



