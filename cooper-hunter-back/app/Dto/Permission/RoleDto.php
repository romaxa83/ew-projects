<?php

namespace App\Dto\Permission;

class RoleDto
{
    /**
     * @var array|RoleTranslateDto[]
     */
    private array $translations;

    private array $permissions;

    private string $name;

    public static function makeByArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->translations = $self::generateTranslatesDto($args['translations']);
        $self->permissions = $args['permissions'];

        return $self;
    }

    private static function generateTranslatesDto(array $translations): array
    {
        $translatesDto = [];
        foreach ($translations as $translation) {
            $translatesDto[] = RoleTranslateDto::fromArgs($translation);
        }
        return $translatesDto;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
