<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialProjects;

use App\Dto\Warranty\WarrantyRegistrationDto;
use App\Entities\Messages\ResponseMessageEntity;
use App\Enums\Warranties\WarrantyType;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectSetWarrantyPermission;
use App\Repositories\Commercial\CommercialProjectRepository;
use App\Services\Warranty\WarrantyService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectSetWarrantyMutation extends BaseMutation
{
    public const NAME = 'commercialProjectSetWarranty';
    public const PERMISSION = CommercialProjectSetWarrantyPermission::KEY;

    public function __construct(
        protected CommercialProjectRepository $repo,
        protected WarrantyService $warrantyService
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(CommercialProject::class, 'id')],
                'description' => "CommercialProjectType - ID"
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
            /** @var $model CommercialProject */
            $model = $this->repo->getByFields(['id' => $args['id']],[],true);

            if(!$model->isEndCommissioning()){
                throw new \Exception(__('exceptions.commercial.warranty.not closed commissioning'));
            }
            if($model->units->isEmpty()){
                throw new \Exception(__('exceptions.commercial.warranty.not have units'));
            }
            if($model->warranty){
                throw new \Exception(__('exceptions.commercial.warranty.exist'));
            }
            if(!$model->additions){
                throw new \Exception(__('exceptions.commercial.warranty.not have additions'));
            }

            $serialNumbers = $model->units->pluck('serial_number')->toArray();
            $dto = WarrantyRegistrationDto::byArgs([
                'type' => WarrantyType::COMMERCIAL,
                'commercial_project_id' => $model->id,
                'technician' => [
                    'first_name' => $model->first_name,
                    'last_name' => $model->last_name,
                    'email' => $model->email,
                    'company_name' => $model->company_name,
                    'company_address' => $model->company_address,
                ],
                'address' => [
                    'country_code' => $model->country->country_code,
                    'state_id' => $model->state_id,
                    'street' => $model->street ?? ' ',
                    'city' => $model->city,
                    'zip' => $model->zip,
                ],
                'product' => [
                    'purchase_date' => $model->additions->purchase_date->format('Y-m-d'),
                    'installation_date' => $model->additions->installation_date->format('Y-m-d'),
                    'installer_license_number' => $model->additions->installer_license_number,
                    'purchase_place' => $model->additions->purchase_place,
                ]
            ]);

            makeTransaction(fn () => $this->warrantyService->registerByUnits(
                $serialNumbers,
                $dto,
                $model->member
            ));

            return ResponseMessageEntity::success(__('messages.commercial.set_warranty'));
        } catch (\Throwable $e){
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}
