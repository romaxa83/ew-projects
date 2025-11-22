<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\Dto\Orders\Dealer\OrderPackingSlipUpdateDto;
use App\GraphQL\InputTypes\Orders\Dealer;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Dealer\PackingSlipType;
use App\Models\Orders\Dealer\PackingSlip;
use App\Permissions\Orders\Dealer\UpdatePermission;
use App\Repositories\Orders\Dealer\PackingSlipRepository;
use App\Services\OneC\RequestService;
use App\Services\Orders\Dealer\PackingSlipService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Error\ValidationError;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdatePackingSlipMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderUpdatePackingSlip';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected PackingSlipService $service,
        protected PackingSlipRepository $repo
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', Rule::exists(PackingSlip::TABLE, 'id')],
                'description' => 'DealerOrderPackingSlipType ID'
            ],
            'packing_slip' => [
                'type' => Dealer\PackingSlipInput::type(),
                'rules' => ['required', 'array']
            ],
            'media' => [
                'type' => Type::listOf(FileType::Type()),
            ],
        ];
    }

    public function type(): Type
    {
        return PackingSlipType::nonNullType();
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
    ): ?PackingSlip
    {
        try {
            $this->isNotForMainDealer();

            /** @var $model PackingSlip */
            $model = $this->repo->getBy('id', $args['id']);

            $this->isOwner($model->order);

            Cache::put('packing_slip_' . $model->guid, [
                'tracking_number' => $model->tracking_number,
                'tracking_company' => $model->tracking_company,
            ], 35);

            $dto = OrderPackingSlipUpdateDto::byArgs($args);

            $model = $this->service->updateDealer($model, $dto);

            $request = app(RequestService::class);
            $request->updateDealerOrderPackingSlip($model);

            $model->refresh();

            return $model;
        } catch (\Throwable $e){

            $validator = validator([], []);
            $validator->errors()->add('media', $e->getMessage());

            throw new ValidationError('validation', $validator);
        }
    }
}
