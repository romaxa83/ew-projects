<?php

namespace Wezom\Cli\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Str;
use Symfony\Component\Console\Input\InputOption;
use Wezom\Cli\Traits\OperatesModulesDirectory;

/**
 * Command to generate settings for module
 *
 * @see SettingsMakeCommand::getOptions()
 */
class SettingsMakeCommand extends GeneratorCommand
{
    use OperatesModulesDirectory;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new settings group';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'SettingGroup';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->bootOperatesModulesDirectoryTrait();
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->input->setOption('multilingual', true);
            $this->input->setOption('mutation', true);
            $this->input->setOption('query', true);
            $this->input->setOption('register-schema', true);
            $this->input->setOption('test', true);
        }

        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        $this->createOrModifyConfig();

        if ($this->option('query')) {
            $this->createQuery();
        }

        if ($this->option('mutation')) {
            $this->createMutation();
        }

        if ($this->option('register-schema')) {
            $this->registerTypes();
        }

        return true;
    }

    protected function createOrModifyConfig(): void
    {
        $path = $this->modulePath('config/settings.php');

        $this->makeDirectory($path);

        $class = $this->qualifyClass($this->getNameInput());

        if ($this->files->exists($path)) {
            $stub = "    DummyClass::class,\n";
            $replacement = [
                [
                    "\n" . 'return [',
                    "use $class;\n",
                ],
                [
                    '];',
                    $this->replaceGroup($stub)->replaceClass($stub, $class),
                ],
            ];

            $content = $this->files->get($path);
            foreach ($replacement as $replace) {
                $content = str_replace($replace[0], $replace[1] . $replace[0], $content);
            }

            $this->files->put($path, $content);

            $this->components->info(sprintf('%s [%s] modified successfully.', 'Config', $path));
        } else {
            $stub = file_get_contents(__DIR__ . '/stubs/settings/config.stub');

            $this->files->put(
                $path,
                $this->replaceGroup($stub)->replaceNamespace($stub, $class)->replaceClass($stub, $class)
            );

            $this->components->info(sprintf('%s [%s] created successfully.', 'Config', $path));
        }
    }

    protected function createMutation(): void
    {
        $name = $this->getNameInput();

        $path = $this->modulePath("src/GraphQL/Mutations/Back/Back{$name}SettingsUpdate.php");

        $this->makeDirectory($path);

        $stub = file_get_contents(
            $this->option('multilingual')
                ? __DIR__ . '/stubs/settings/mutation.translated.stub'
                : __DIR__ . '/stubs/settings/mutation.stub'
        );

        $class = $this->qualifyClass($name);
        $this->files->put(
            $path,
            $this->replaceModule($stub)->replaceNamespace($stub, $class)->replaceClass($stub, $class)
        );

        $this->components->info(sprintf('%s [%s] created successfully.', 'Mutation', $path));

        if ($this->option('test')) {
            $this->createMutationTest($name);
        }
    }

    protected function createMutationTest(string $name): void
    {
        $path = $this->modulePath("tests/Feature/Mutations/Back/Back{$name}SettingsUpdateTest.php");

        $this->makeDirectory($path);

        $stub = file_get_contents(
            $this->option('multilingual')
                ? __DIR__ . '/stubs/settings/tests/mutation.translated.stub'
                : __DIR__ . '/stubs/settings/tests/mutation.stub'
        );

        $class = $this->qualifyClass($name);
        $this->files->put(
            $path,
            $this->replaceModule($stub)->replacePermissionGroup($stub)->replaceClass($stub, $class)
        );

        $this->components->info(sprintf('%s [%s] created successfully.', 'Mutation test', $path));
    }

    protected function createQuery(): void
    {
        $name = $this->getNameInput();

        $path = $this->modulePath("src/GraphQL/Queries/Back/Back{$name}Settings.php");

        $this->makeDirectory($path);

        $stub = file_get_contents(__DIR__ . '/stubs/settings/query.stub');

        $class = $this->qualifyClass($name);
        $this->files->put(
            $path,
            $this->replaceModule($stub)
                ->replaceName($stub)
                ->replaceGroup($stub)
                ->replacePermissionGroup($stub)
                ->replaceClass($stub, $class)
        );

        $this->components->info(sprintf('%s [%s] created successfully.', 'Query', $path));

        if ($this->option('test')) {
            $this->createQueryTest($name);
        }
    }

    protected function createQueryTest(string $name): void
    {
        $path = $this->modulePath("tests/Feature/Queries/Back/Back{$name}SettingsQueryTest.php");

        $this->makeDirectory($path);

        $stub = file_get_contents(
            $this->option('multilingual')
                ? __DIR__ . "/stubs/settings/tests/query.translated.stub"
                : __DIR__ . "/stubs/settings/tests/query.stub"
        );

        $class = $this->qualifyClass($name);
        $this->files->put(
            $path,
            $this->replaceModule($stub)->replacePermissionGroup($stub)->replaceClass($stub, $class)
        );

        $this->components->info(sprintf('%s [%s] created successfully.', 'Query test', $path));
    }

    protected function registerTypes(): void
    {
        if (!$this->option('query') && !$this->option('mutation')) {
            $this->components->alert('Type register needs at least one query or mutation');

            return;
        }

        $path = $this->modulePath('graphql/schema.graphql');

        $this->makeDirectory($path);

        if (!$this->files->exists($path)) {
            $this->files->put(
                $path,
                file_get_contents(__DIR__ . '/stubs/module/schema.stub')
            );
        }

        $content = $this->files->get($path);

        // Register query
        if ($this->option('query')) {
            $this->insertQuery($content);

            $content .= file_get_contents(
                $this->option('multilingual')
                    ? __DIR__ . '/stubs/settings/schema-type.translated.stub'
                    : __DIR__ . '/stubs/settings/schema-type.stub'
            );
        }

        // Register mutation
        if ($this->option('mutation')) {
            $this->insertMutation($content);

            $content .= file_get_contents(
                $this->option('multilingual')
                    ? __DIR__ . '/stubs/settings/schema-input.translated.stub'
                    : __DIR__ . '/stubs/settings/schema-input.stub'
            );
        }

        $name = $this->getNameInput();

        $this->files->put(
            $path,
            $this->replaceGroup($content)
                ->replaceName($content)
                ->replacePermissionGroup($content)
                ->replaceClass($content, $name)
        );

        $this->components->info(sprintf('%s [%s] modified successfully.', 'Schema', $path));
    }

    protected function insertMutation(&$content): void
    {
        $pattern = '/(extend type Mutation.*?\{.*?)}/s';

        preg_match($pattern, $content, $matches);

        if (empty($matches)) {
            $content .= "\nextend type Mutation {}\n";
        }

        $part = /** @lang GraphQL */ <<<'TEXT'
    backDummyClassSettingsUpdate(
        input: DummyClassSettingsInput!
    ): [ResponseMessageType!]!
TEXT;

        $content = preg_replace($pattern, "\$1\n$part\n}", $content);
    }

    protected function insertQuery(&$content): void
    {
        $pattern = '/(extend type Query.*?\{.*?)}/s';

        preg_match($pattern, $content, $matches);

        if (empty($matches)) {
            $content .= "\nextend type Query {}\n";
        }

        $part = /** @lang GraphQL */ <<<'TEXT'
    backDummyClassSettings: DummyClassSetting!
TEXT;

        $content = preg_replace($pattern, "\$1\n$part\n}", $content);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $this->getNameInput());

        return $this->modulePath('src/Settings/' . str_replace('\\', '/', $name) . '.php');
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->option('multilingual')
            ? __DIR__ . '/stubs/settings/group.translated.stub'
            : __DIR__ . '/stubs/settings/group.stub';
    }

    protected function replaceGroup(&$stub): static
    {
        $stub = str_replace(['DummyGroup', '{{ group }}', '{{group}}'], Str::snake($this->getNameInput()), $stub);

        return $this;
    }

    protected function replacePermissionGroup(&$stub): static
    {
        $stub = str_replace(
            ['PermissionGroup', '{{ permissions }}', '{{permissions}}'],
            Str::kebab($this->getNameInput()),
            $stub
        );

        return $this;
    }

    protected function replaceName(&$stub): static
    {
        $stub = str_replace(['DummyName', '{{ name }}', '{{name}}'], Str::camel($this->getNameInput()), $stub);

        return $this;
    }

    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        return 'Wezom\\' . Str::studly($this->argument('module')) . '\\Settings\\';
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate multilingual setting group with all default actions'],
            ['multilingual', 'm', InputOption::VALUE_NONE, 'Make multilingual group'],
            ['query', 'Q', InputOption::VALUE_NONE, 'Generate settings Query'],
            ['mutation', 'M', InputOption::VALUE_NONE, 'Generate Mutation to update settings'],
            ['register-schema', 'r', InputOption::VALUE_NEGATABLE, 'Try to register types in module`s schema'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['test', null, InputOption::VALUE_NONE, 'Generate respective test class(es) for mutation and query'],
        ];
    }
}
