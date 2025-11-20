<?php

namespace App\GraphQL\Mutations\BackOffice\Sips;

use App\GraphQL\Types\NonNullType;
use App\Models\Sips\Sip;
use App\Permissions;
use App\Services\Sips\SipService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SipDeleteMutation extends BaseMutation
{
    public const NAME = 'SipDelete';
    public const PERMISSION = Permissions\Sips\DeletePermission::KEY;

    public function __construct(
        protected SipService $service
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null): bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Sip::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
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
    ): bool
    {
        /** @var $model Sip */
        $model = $this->service->repo->getBy('id', $args['id']);

        if($model->employee){
            throw new TranslatedException(__('exceptions.sip.cant_delete_exist_employee'), 502);
        }

        return $this->service->delete($model);
    }
}
