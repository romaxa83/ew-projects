<?php

namespace App\GraphQL\Mutations\FrontOffice\Fcm;

use App\GraphQL\Types\NonNullType;
use App\Permissions\Fcm\FcmAddPermission;
use App\Services\Fcm\FcmService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FcmTokenAddMutation extends BaseMutation
{
    public const NAME = 'fcmTokenAdd';
    public const PERMISSION = FcmAddPermission::KEY;

    public function __construct(private FcmService $service)
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    public function args(): array
    {
        return [
            'fcm_token' => [
                'type' => NonNullType::string()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     * diAiePIHRLOLr4-lJcdg_4:APA91bESbsOzOFKPTGZIIX3-l5kr_9HH4vWjYls6UGGZ9aX9BoMAZwmzrt_Kmtmbd1eP2r3VY8Z4l2OOu2MPPticKAiYv3-n_HfPk3l0ellPMryWNENNRK80xO5R2Tmbq0tvqqqX6b42
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        makeTransaction(
            fn() => $this->service->saveToken($this->user(), $args['fcm_token'])
        );
        return true;
    }
}
