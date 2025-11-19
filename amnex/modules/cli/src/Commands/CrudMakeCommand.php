<?php

namespace Wezom\Cli\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Validation\Rule;
use Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Wezom\Cli\Traits\OperatesModulesDirectory;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Traits\Services\InteractsWithTranslations;

#[AsCommand('make:crud', 'Create crud to model')]
class CrudMakeCommand extends Command
{
    use OperatesModulesDirectory;

    protected const string ENUMS_DIR = 'Enums';
    protected const string DTO_DIR = 'Dto';
    protected const string SERVICES_DIR = 'Services';
    protected const string MODELS_DIR = 'Models';
    protected const string BACK_MUTATION_DIR = 'GraphQL/Mutations/Back';
    protected const string BACK_QUERY_DIR = 'GraphQL/Queries/Back';
    protected const string SITE_QUERY_DIR = 'GraphQL/Queries/Site';
    protected const string TESTS_PROJECTION_DIR = 'GraphQL/Projections';
    protected const string TESTS_MUTATIONS_BACK_DIR = 'Feature/Mutations/Back';
    protected const string TESTS_QUERIES_BACK_DIR = 'Feature/Queries/Back';
    protected const string TESTS_QUERIES_SITE_DIR = 'Feature/Queries/Site';

    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();

        $this->getDefinition()->addArgument(
            new InputArgument('name', InputArgument::REQUIRED, 'The model name')
        );

        $this->bootOperatesModulesDirectoryTrait();
    }

    protected function getOptions(): array
    {
        return [
            ['site-query', 'Q', InputOption::VALUE_NONE, 'Create site{ModelName} query'],
            ['sort', 's', InputOption::VALUE_NONE, 'Model should be sortable'],
            ['multilingual', 'm', InputOption::VALUE_NONE, 'Model should be multilingual'],
        ];
    }

    public function handle(): ?bool
    {
        $this->createOrderColumnEnum();
        $this->createDto();
        $this->createService();
        $this->createGraphQlFieldResolvers();
        $this->createSchema();
        $this->createTests();

        return true;
    }

    protected function createOrderColumnEnum(): void
    {
        $cases = [
            "case ID = 'id';",
        ];

        if ($this->option('sort')) {
            $cases[] = "case SORT = 'sort';";
        }

        $targetClassName = $this->getOrderEnumClassName();

        $this->createFile('order-column-enum.stub', static::ENUMS_DIR, $targetClassName, [
            '{{cases}}' => implode("\n    ", $cases),
        ]);
    }

    protected function getNameInput(): string
    {
        return str($this->argument('name'))->trim()->value();
    }

    protected function rootNamespace(): string
    {
        return 'Wezom\\' . Str::studly($this->argument('module')) . '\\';
    }

    protected function createFile(
        string $stub,
        string $subDir,
        string $targetClassName,
        array $replace = [],
        bool $testDir = false
    ): void {
        $targetFile = $this->getTargetFilePath($subDir, $targetClassName, $testDir ? 'tests' : 'src');
        if (is_file($targetFile)) {
            $this->warn('File ' . $subDir . '/' . $targetClassName . ' already exists.');

            return;
        }

        $this->makeDirectory($targetFile);

        $replace['DummyNamespace'] = $this->getNamespace($testDir ? 'Tests/' . $subDir : $subDir);
        $replace['DummyClass'] = $targetClassName;

        $stubContent = $this->replaceStubContent($stub, $replace);

        $this->files->put($targetFile, $stubContent);
    }

    protected function getTargetFilePath(string $subDirName, string $name, string $baseDir): string
    {
        $name = (string)Str::of($name)
            ->replaceFirst($this->rootNamespace(), '')
            ->start($subDirName . '/')
            ->replace('\\', '/');

        return $this->modulePath($baseDir . '/' . $name . '.php');
    }

    protected function makeDirectory($path): string
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    protected function getNamespace(?string $subDir): string
    {
        return $this->rootNamespace() . str_replace('/', '\\', $subDir);
    }

    protected function createDto(): void
    {
        $targetClassName = $this->getDtoBaseName();
        $translationTargetClassName = $this->getDtoTranslationBaseName();

        if ($this->option('multilingual')) {
            $this->createFile('root-dto-translation.stub', static::DTO_DIR, $targetClassName, [
                '{{TranslationsDtoClass}}' => $translationTargetClassName,
            ]);

            $this->createFile('dto-translation.stub', static::DTO_DIR, $translationTargetClassName);

            return;
        }

        $this->createFile('root-dto.stub', static::DTO_DIR, $targetClassName);
    }

    protected function createService(): void
    {
        $targetClassName = $this->getServiceBaseName();

        $replace = [
            'DtoClass' => $this->getDtoBaseName(),
            'DtoTranslationClass' => $this->getDtoTranslationBaseName(),
        ];

        $dtoClassName = $this->rootNamespace() . static::DTO_DIR . '\\' . $this->getDtoBaseName();

        if ($this->option('multilingual')) {
            $replace['{{uses}}'] = $this->buildUses(
                $dtoClassName,
                $this->rootNamespace() . static::DTO_DIR . '\\' . $this->getDtoTranslationBaseName(),
                InteractsWithTranslations::class,
            );

            $this->createFile('service-translated.stub', static::SERVICES_DIR, $targetClassName, $replace);

            return;
        }

        $replace['{{uses}}'] = $this->buildUses($dtoClassName);

        $this->createFile('service.stub', static::SERVICES_DIR, $targetClassName, $replace);
    }

    protected function createGraphQlFieldResolvers(): void
    {
        $replace = [
            'ServiceClass' => $this->getServiceBaseName(),
            'DtoClass' => $this->getDtoBaseName(),
        ];

        $replace['{{uses}}'] = $this->buildUses(
            BackFieldResolver::class,
            Context::class,
            Rule::class,
            Ability::class,
            $this->modelClassName(),
            $this->getServiceClassName(),
            $this->getDtoClassName(),
        );

        $pluralNameInput = $this->getPluralNameInput();

        $this->createFile(
            'create-field-resolver.stub',
            static::BACK_MUTATION_DIR . '/' . $pluralNameInput,
            $this->getBackCreateBaseName(),
            $replace
        );

        $this->createFile(
            'update-field-resolver.stub',
            static::BACK_MUTATION_DIR . '/' . $pluralNameInput,
            $this->getBackUpdateBaseName(),
            $replace
        );

        $this->createFile(
            'back-query-field-resolver.stub',
            static::BACK_QUERY_DIR . '/' . $pluralNameInput,
            $this->getBackQueryBaseName(),
            $replace
        );

        if ($this->option('site-query')) {
            $this->createFile(
                'site-query-field-resolver.stub',
                static::SITE_QUERY_DIR . '/' . $pluralNameInput,
                $this->getSiteQueryBaseName()
            );
        }
    }

    protected function createSchema(): void
    {
        $ability = Ability::key(str($this->getNameInput())->snake('-')->plural());

        $replace = [
            '{{EntityType}}' => $this->getNameInput(),
            '{{BackQueryName}}' => camel_case($this->getBackQueryBaseName()),
            '{{ModelClassName}}' => $this->toGraphqlClassName(
                static::MODELS_DIR,
                $this->getNameInput()
            ),
            '{{queries}}' => $this->option('site-query') ? $this->createSiteQuerySchema() : null,
            '{{defaultOrderColumn}}' => 'ID',
            '{{defaultOrderDirection}}' => 'DESC',
            '{{mutations}}' => $this->option('sort') ? $this->createUpdateSortSchema() : null,
            '{{types}}' => $this->createTypesSchema(),
            '{{BackCreateMutationName}}' => camel_case($this->getBackCreateBaseName()),
            '{{BackUpdateMutationName}}' => camel_case($this->getBackUpdateBaseName()),
            '{{BackDeleteMutationName}}' => camel_case($this->getBackDeleteBaseName()),
            '{{BackToggleActiveMutationName}}' => camel_case($this->getBackToggleActiveBaseName()),
            '{{viewPermission}}' => $ability->viewAction()->build(),
            '{{createPermission}}' => $ability->createAction()->build(),
            '{{updatePermission}}' => $ability->updateAction()->build(),
            '{{deletePermission}}' => $ability->deleteAction()->build(),
        ];

        if ($this->option('sort')) {
            $replace['{{defaultOrderColumn}}'] = 'SORT';
            $replace['{{defaultOrderDirection}}'] = 'ASC';
        }

        // todo merge schemas
        $targetFile = $this->modulePath('graphql')
            . '/'
            . str($this->getNameInput())->snake('-')->plural()
            . '.graphql';
        if (is_file($targetFile)) {
            $this->warn('File ' . $targetFile . ' already exists.');

            return;
        }

        $this->makeDirectory($targetFile);

        $stubContent = $this->replaceStubContent('schema/schema.stub', $replace);

        $this->files->put($targetFile, $stubContent);
    }

    private function createSiteQuerySchema(): string
    {
        return $this->replaceStubContent('schema/site-query-schema.stub', [
            '{{SiteQueryName}}' => camel_case($this->getSiteQueryBaseName()),
            '{{EntityType}}' => $this->getNameInput(),
        ]);
    }

    private function createUpdateSortSchema(): string
    {
        return $this->replaceStubContent('schema/back-update-sort-schema.stub', [
            '{{BackUpdateSortMutationName}}' => camel_case($this->getBackUpdateSortBaseName()),
            '{{ModelClassName}}' => $this->toGraphqlClassName(
                static::MODELS_DIR,
                $this->getNameInput()
            ),
        ]);
    }

    private function createTypesSchema(): string
    {
        $schema = 'type';
        if ($this->option('multilingual')) {
            $schema .= '-translated';
        }

        return $this->replaceStubContent('schema/' . $schema . '-schema.stub', [
            '{{EntityType}}' => $this->getNameInput(),
        ]);
    }

    protected function getDtoBaseName(): string
    {
        return $this->getNameInput() . 'Dto';
    }

    protected function getDtoClassName(): string
    {
        return $this->rootNamespace() . static::DTO_DIR . '\\' . $this->getDtoBaseName();
    }

    protected function getDtoTranslationBaseName(): string
    {
        return $this->getNameInput() . 'TranslationDto';
    }

    protected function getServiceBaseName(): string
    {
        return $this->getNameInput() . 'Service';
    }

    protected function getServiceClassName(): string
    {
        return $this->rootNamespace() . static::SERVICES_DIR . '\\' . $this->getServiceBaseName();
    }

    protected function getOrderEnumClassName(): string
    {
        return $this->getNameInput() . 'OrderColumnEnum';
    }

    private function getNamespacedOrderEnumClassName(): string
    {
        return $this->getNamespace(self::ENUMS_DIR . '/' . $this->getOrderEnumClassName());
    }

    protected function getBackCreateBaseName(): string
    {
        return 'Back' . $this->getNameInput() . 'Create';
    }

    protected function getBackUpdateBaseName(): string
    {
        return 'Back' . $this->getNameInput() . 'Update';
    }

    protected function getBackDeleteBaseName(): string
    {
        return 'Back' . $this->getNameInput() . 'Delete';
    }

    protected function getBackToggleActiveBaseName(): string
    {
        return 'Back' . $this->getNameInput() . 'ToggleActive';
    }

    protected function getBackUpdateSortBaseName(): string
    {
        return 'Back' . $this->getNameInput() . 'UpdateSort';
    }

    protected function getBackQueryBaseName(): string
    {
        return 'Back' . $this->getPluralNameInput();
    }

    protected function getPluralNameInput(): string
    {
        return str_plural($this->getNameInput());
    }

    protected function getSiteQueryBaseName(): string
    {
        return 'Site' . $this->getPluralNameInput();
    }

    protected function getProjectionBaseName(): string
    {
        return $this->getNameInput() . 'Projection';
    }

    protected function getProjectionTranslationBaseName(): string
    {
        return $this->getNameInput() . 'TranslationProjection';
    }

    protected function modelClassName(): string
    {
        return $this->rootNamespace() . static::MODELS_DIR . '\\' . $this->getNameInput();
    }

    protected function buildUses(string ...$array): string
    {
        return collect($array)->map(fn (string $item) => "use $item;")->sort()->implode("\n");
    }

    private function toGraphqlClassName(string $dir, string $className): string
    {
        return str($dir)
            ->prepend($this->rootNamespace())
            ->replace('/', '\\')
            ->append('\\', $className)
            ->replace('\\', '\\\\')
            ->value();
    }

    protected function replaceStubContent(string $stub, array $replace): string|array
    {
        $stubContent = $this->files->get(__DIR__ . '/stubs/crud/' . $stub);

        $replace['{{namespacedModel}}'] = $this->modelClassName();
        $replace['ModelClass'] = $this->getNameInput();

        return str_replace(array_keys($replace), array_values($replace), $stubContent);
    }

    private function createTests(): void
    {
        $this->createProjections();

        $this->createMutations();

        $this->createBackQuery();
        $this->createSiteQuery();
    }

    private function createProjections(): void
    {
        $this->createFile(
            'tests/projections/projection.stub',
            self::TESTS_PROJECTION_DIR,
            $this->getProjectionBaseName(),
            testDir: true
        );

        if (!$this->option('multilingual')) {
            return;
        }

        $this->createFile(
            'tests/projections/projection-translation.stub',
            self::TESTS_PROJECTION_DIR,
            $this->getProjectionTranslationBaseName(),
            testDir: true
        );
    }

    private function createMutations(): void
    {
        $replace = [
            '{{namespacedProjection}}' => $this->getNamespace('Tests/' . self::TESTS_PROJECTION_DIR),
            '{{ProjectionName}}' => $this->getProjectionBaseName(),
            '{{ProjectionTranslatedName}}' => $this->getProjectionTranslationBaseName(),
        ];

        $nameInput = $this->getNameInput();

        $multilingual = $this->option('multilingual');

        $this->createFile(
            'tests/mutations/' . ($multilingual ? 'create-multilingual' : 'create') . '.stub',
            self::TESTS_MUTATIONS_BACK_DIR,
            $this->getBackCreateBaseName() . 'Test',
            $replace,
            true
        );

        $this->createFile(
            'tests/mutations/' . ($multilingual ? 'update-multilingual' : 'update') . '.stub',
            self::TESTS_MUTATIONS_BACK_DIR,
            $this->getBackUpdateBaseName() . 'Test',
            $replace,
            true
        );

        $this->createFile(
            'tests/mutations/delete.stub',
            self::TESTS_MUTATIONS_BACK_DIR,
            $this->getBackDeleteBaseName() . 'Test',
            $replace,
            true
        );

        $this->createFile(
            'tests/mutations/toggle-active.stub',
            self::TESTS_MUTATIONS_BACK_DIR,
            $this->getBackToggleActiveBaseName() . 'Test',
            $replace,
            true
        );

        if ($this->option('sort')) {
            $this->createFile(
                'tests/mutations/update-sort.stub',
                self::TESTS_MUTATIONS_BACK_DIR,
                'Back' . $nameInput . 'UpdateSortTest',
                $replace,
                true
            );
        }
    }

    private function createBackQuery(): void
    {
        $replace = [
            '{{methods}}' => $this->makeTestSortMethod(),
            '{{OrderColumnEnum}}' => $this->getOrderEnumClassName(),
            '{{namespacedOrderColumnEnum}}' => $this->getNamespacedOrderEnumClassName(),
            '{{namespacedProjection}}' => $this->getNamespace('Tests/' . self::TESTS_PROJECTION_DIR),
            '{{ProjectionName}}' => $this->getProjectionBaseName(),
            '{{ProjectionTranslatedName}}' => $this->getProjectionTranslationBaseName(),
        ];

        $multilingual = $this->option('multilingual');

        $this->createFile(
            'tests/queries/back-query' . ($multilingual ? '-multilingual' : '') . '.stub',
            self::TESTS_QUERIES_BACK_DIR,
            $this->getBackQueryBaseName() . 'Test',
            $replace,
            true
        );
    }

    private function makeTestSortMethod(): ?string
    {
        if (!$this->option('sort')) {
            return null;
        }

        return $this->replaceStubContent(
            'tests/queries/test-order-by-sort-method.stub',
            [
                '{{OrderColumnEnum}}' => $this->getOrderEnumClassName(),
            ]
        );
    }

    private function createSiteQuery(): void
    {
        if (!$this->option('site-query')) {
            return;
        }

        $replace = [
            '{{methods}}' => $this->makeTestSortMethod(),
            '{{OrderColumnEnum}}' => $this->getOrderEnumClassName(),
            '{{namespacedOrderColumnEnum}}' => $this->getNamespacedOrderEnumClassName(),
            '{{namespacedProjection}}' => $this->getNamespace('Tests/' . self::TESTS_PROJECTION_DIR),
            '{{ProjectionName}}' => $this->getProjectionBaseName(),
            '{{ProjectionTranslatedName}}' => $this->getProjectionTranslationBaseName(),
        ];

        $multilingual = $this->option('multilingual');

        $this->createFile(
            'tests/queries/site-query' . ($multilingual ? '-multilingual' : '') . '.stub',
            self::TESTS_QUERIES_SITE_DIR,
            $this->getSiteQueryBaseName() . 'Test',
            $replace,
            true
        );
    }
}
