<?php

namespace App\GraphQL\Mutations\FrontOffice\Inspections;

use App\GraphQL\InputTypes\Inspection\InspectionTirePhotosInputType;
use App\GraphQL\Types\Inspections\InspectionTireType;
use App\GraphQL\Types\NonNullType;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Permissions\Inspections\InspectionCreatePermission;
use App\Services\Inspections\InspectionTireService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Http\File as FileModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;
use Illuminate\Http\File;

class TestTireUploadMutation extends BaseMutation
{
    public const NAME = 'testTireUpload';
    public const PERMISSION = InspectionCreatePermission::KEY;

    public function __construct(protected InspectionTireService $service)
    {
        $this->setUserGuard();
    }

    public function args(): array
    {
        return [
            'inspection_tire_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(InspectionTire::class, 'id')],
            ],
            'file' => [
                'type' => NonNullType::string(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'ext' => [
                'type' => NonNullType::string(),
            ],
        ];
    }

    public function type(): Type
    {
        return InspectionTireType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Inspection
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): InspectionTire
    {
        $model = InspectionTire::find($args['inspection_tire_id']);

        $pathStorage = Storage::disk('public')
            ->getDriver()
            ->getAdapter()
            ->getPathPrefix();

        if (!file_exists("{$pathStorage}temp")) {
            mkdir("{$pathStorage}temp", 0777, true);
        }

        $basename = $args['name'] . '.' . $args['ext'];
        $filename = "{$pathStorage}temp/$basename";

        file_put_contents($filename, base64_decode($args['file']));

        $img = new File($filename);

        $model->clearMediaCollection(InspectionTire::PHOTO_MAIN)
            ->copyMedia($img)
            ->toMediaCollection(InspectionTire::PHOTO_MAIN);

        Storage::deleteDirectory('temp');

        return $model;

//        dd($pathStorage, $filename);
//        dd($args);
//        return makeTransaction(
//            fn() => $this->service->upload(
//                InspectionTire::find($args['inspection_tire_id']),
//                $args['photos']
//            )
//        );
    }
}

