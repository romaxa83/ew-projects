<?php

namespace Tests\Unit\Repository;

use App\Models\Translate;
use App\Models\User\Role;
use App\Repositories\TranslationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\TranslationBuilder;
use Tests\TestCase;

class TranslationRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected $translationBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationBuilder = resolve(TranslationBuilder::class);
    }

    /** @test */
    public function check_exist_by_alias_and_lang(): void
    {
        $this->translationBuilder->setModel(Translate::TYPE_SITE)
            ->setAlias('text')->setLang('en')->create();

        $repo = app(TranslationRepository::class);

        $this->assertTrue($repo->existByAliasAndLang('text', 'en'));
        $this->assertFalse($repo->existByAliasAndLang('text', 'ru'));
    }

    /** @test */
    public function check_exist_role_by_entity_id(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $repo = app(TranslationRepository::class);

        $this->assertTrue($repo->existRoleByEntityId($role->id));
        $this->assertFalse($repo->existRoleByEntityId(99999));
    }

    /** @test */
    public function check_exist_for_copy(): void
    {
        $obj = new \stdClass;
        $obj->model = Translate::TYPE_SITE;
        $obj->alias = 'text';
        $obj->group = 'group';

        $this->translationBuilder
            ->setModel(Translate::TYPE_SITE)
            ->setAlias('text')
            ->setLang('en')
            ->setGroup('group')
            ->create();

        $repo = app(TranslationRepository::class);

        $this->assertTrue($repo->existForCopy($obj, 'en'));
        $this->assertfalse($repo->existForCopy($obj, 'ru'));
    }

    /** @test */
    public function check_exist_for_copy_with_entity(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $obj = new \stdClass;
        $obj->model = Translate::TYPE_SITE;
        $obj->alias = 'text';
        $obj->group = 'group';
        $obj->entity_type = Role::class;
        $obj->entity_id = $role->id;

        $this->translationBuilder
            ->setEntity($role)
            ->setModel(Translate::TYPE_SITE)
            ->setAlias('text')
            ->setLang('en')
            ->setGroup('group')
            ->create();

        $repo = app(TranslationRepository::class);

        $this->assertTrue($repo->existForCopy($obj, 'en'));
        $this->assertfalse($repo->existForCopy($obj, 'ru'));
    }
}

