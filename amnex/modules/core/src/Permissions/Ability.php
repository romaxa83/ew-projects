<?php

declare(strict_types=1);

namespace Wezom\Core\Permissions;

use Illuminate\Database\Eloquent\Model;
use Stringable;
use Wezom\Core\Enums\PermissionActionEnum;

class Ability implements Stringable
{
    public function __construct(private readonly ?string $key, private PermissionActionEnum|string|null $action)
    {
    }

    /**
     * @param  class-string<Model>|Model  $class
     */
    public static function toModel(string|Model $class): Ability
    {
        if (is_object($class)) {
            $class = $class::class;
        }

        return static::key($class);
    }

    public static function key(string $key): Ability
    {
        return new Ability($key, null);
    }

    /**
     * @param  string|class-string<Model>  $key
     */
    public static function editSettings(string $key): Ability
    {
        if (is_a($key, Model::class, true)) {
            $key = (new $key())->getTable();
        }

        return static::key($key)->editSettingsAction();
    }

    public function createAction(): Ability
    {
        return $this->action(PermissionActionEnum::CREATE);
    }

    public function viewAction(): Ability
    {
        return $this->action(PermissionActionEnum::VIEW);
    }

    public function updateAction(): Ability
    {
        return $this->action(PermissionActionEnum::UPDATE);
    }

    public function deleteAction(): Ability
    {
        return $this->action(PermissionActionEnum::DELETE);
    }

    public function editSettingsAction(): Ability
    {
        return $this->action(PermissionActionEnum::EDIT_SETTINGS);
    }

    public function action(PermissionActionEnum|string $action): Ability
    {
        $this->action = $action;

        return $this;
    }

    public function __toString(): string
    {
        return $this->build();
    }

    public function build(): string
    {
        return Permissions::transformKey($this->key)
            . '.'
            . ($this->action instanceof PermissionActionEnum ? $this->action->value : $this->action);
    }

    public function getModel(): ?string
    {
        return is_a($this->key, Model::class, true) ? $this->key : null;
    }

    public function getAction(): PermissionActionEnum|string|null
    {
        return $this->action;
    }
}
