<?php

namespace WezomCms\Users\Types;

use WezomCms\Core\Types\AbstractStatus;
use WezomCms\Users\Types\Exception\UserStatusException;

final class UserStatus extends AbstractStatus
{
    const DRAFT = 1;   // только создан

    public static function list(): array
    {
        return [
            self::DRAFT => __('cms-users::admin.status.Draft'),
        ];
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    protected static function exceptionMessage($status = null): void
    {
        throw new UserStatusException(
            __('cms-users::admin.exception.Invalid user status', [
                'status' => $status
            ])
        );
    }

    public function render(): string
    {
        return '<span class="badge badge-warning">'. self::list()[self::DRAFT].'</span>';
    }
}


