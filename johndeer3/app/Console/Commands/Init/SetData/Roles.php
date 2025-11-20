<?php

namespace App\Console\Commands\Init\SetData;

use App\DTO\Locale\TranslationDTO;
use App\Models\Translate;
use App\Models\User\Role;
use App\Repositories\LanguageRepository;
use App\Repositories\TranslationRepository;
use App\Repositories\User\RoleRepository;
use App\Services\Translations\TranslationService;

class Roles
{
    public function __construct(
        protected RoleRepository $roleRepository,
        protected LanguageRepository $languageRepository,
        protected TranslationRepository $translateRepository,
        protected TranslationService $translateService
    )
    {}

    public function run(): void
    {
        $this->setRoles();
    }

    private function setRoles(): void
    {
        foreach (Role::getRoles() as $role){
            $model = $this->roleRepository->getBy('role', $role);
            if(!$model){
                $model = new Role();
                $model->role = $role;
                $model->save();

                echo "Set role - [{$role}]" . PHP_EOL;
            }

            foreach ($this->languageRepository->getForSelect() as $locale => $name) {
                if(!$this->translateRepository->existRoleByEntityId($model->id, $locale)){
                    $this->translateService->create(TranslationDTO::byArgs([
                        'model' => Translate::TYPE_ROLE,
                        'entity_type' => Role::class,
                        'entity_id' => $model->id,
                        'text' => $role . "__(translate into {$name})",
                        'lang' => $locale
                    ]));

//                    echo "Set role [{$role}] translation for lang - [{$locale}]" . PHP_EOL;
                }
            }
        }
    }
}

