<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Entities\Messages\ResponseMessageEntity;
use App\Exceptions\Admin\CantDeleteByMyselfException;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminDeletePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminDeleteMutation extends BaseMutation
{
    public const NAME = 'adminDelete';
    public const PERMISSION = AdminDeletePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function args(): array
    {
        return [
            'ids' => NonNullType::listOf(NonNullType::id()),
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity {
        try {
            foreach ($args['ids'] as $id) {
                if ($this->authId() === (int)$id) {
                    throw new CantDeleteByMyselfException(
                        __('messages.admin.actions.delete.fail.reasons.by-myself')
                    );
                }
            }

            make_transaction(static fn() => Admin::query()->whereKey($args['ids'])->delete());

            $keyForTranslate = count($args['ids']) > 1
                ? __('messages.admin.actions.delete.success.many-entity')
                : __('messages.admin.actions.delete.success.one-entity');

            return ResponseMessageEntity::success($keyForTranslate);
        } catch (CantDeleteByMyselfException $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        } catch (Throwable) {
            return ResponseMessageEntity::fail(__('exceptions.default'));
        }
    }

    protected function rules(array $args = []): array
    {
        return $this->guest()
            ? []
            : [
                'ids' => ['required', 'array'],
                'ids.*' => ['required', 'integer', 'exists:admins,id'],
            ];
    }
}
