<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings;

use App\GraphQL\Types\FileType;
use App\Models\Commercial\CommercialSettings;
use App\Permissions\Commercial\CommercialSettings\CommercialSettingsUpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class CommercialSettingsRDPUploadMutation extends BaseMutation
{
    public const NAME = 'commercialSettingsRDPUpload';
    public const PERMISSION = CommercialSettingsUpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'rdp' => [
                'type' => FileType::nonNullType(),
                'rules' => ['file'],
            ]
        ];
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     * @throws FileCannotBeAdded
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $settings = CommercialSettings::firstOrFail();

        try {
            $settings->addMedia($args['rdp'])
                ->toMediaCollection($settings::MEDIA_RDP);
        } catch (FileCannotBeAdded $exception) {
            throw $exception;
        } catch (Throwable $e) {
            logger($e);

            return false;
        }

        return true;
    }
}
