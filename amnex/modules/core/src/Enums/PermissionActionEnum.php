<?php

declare(strict_types=1);

namespace Wezom\Core\Enums;

enum PermissionActionEnum: string
{
    case CREATE = 'create';
    case VIEW = 'view';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case EDIT_SETTINGS = 'edit-settings';
}
