<?php

namespace App\Dashboard\Widgets;

use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\Models\BaseAuthenticatable;

abstract class AbstractWidget
{
    public const PERMISSION = null;

    protected BaseAuthenticatable $user;

    public static function buildFor(BaseAuthenticatable $user): static
    {
        return app(static::class)->for($user);
    }

    public function for(BaseAuthenticatable $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function authorize(): bool
    {
        if (!is_null($p = static::PERMISSION)) {
            return $this->user->can($p);
        }

        return true;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    abstract public function getTitle(): string;

    abstract public function getValue(): string;

    abstract public function getSection(): DashboardWidgetSectionEnum;

    abstract public function getType(): DashboardWidgetTypeEnum;
}