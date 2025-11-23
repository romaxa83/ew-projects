<?php

namespace WezomCms\Providers\Types;

use WezomCms\Core\Types\AbstractStatus;
use WezomCms\Providers\Types\Exception\ProviderStatusException;

final class ProviderStatus extends AbstractStatus
{
    const DRAFT     = 1;   // создан, но не прошел модерацию
    const MODERATED = 2;   // прошел модерацию

    public static function list(): array
    {
        return [
            self::DRAFT => __('cms-providers::admin.provider.status.Draft'),
            self::MODERATED => __('cms-providers::admin.provider.status.Moderated'),
        ];
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public static function createDraft(): self
    {
        return self::create(self::DRAFT);
    }


    public function isModerate(): bool
    {
        return $this->value === self::MODERATED;
    }

    protected static function exceptionMessage($status = null): void
    {
        throw new ProviderStatusException(
            __('cms-providers::admin.exception.Invalid provider status', [
                'status' => $status
            ])
        );
    }

    public function render(): string
    {
        if($this->isDraft()){
            return '<span class="badge badge-warning">'. self::list()[self::DRAFT].'</span>';
        }
        return '<span class="badge badge-success">'. self::list()[self::MODERATED].'</span>';
    }
}


