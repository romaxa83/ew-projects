<?php

namespace Wezom\Cli\Commands;

use Arr;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;

use function Laravel\Prompts\multiselect;

use Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wezom\Cli\Traits\OperatesModulesDirectory;
use Wezom\Core\Traits\Model\ActiveScopeTrait;
use Wezom\Core\Traits\Model\Filterable;
use Wezom\Core\Traits\Model\HasTranslations;
use Wezom\Core\Traits\Model\TranslationTrait;

/**
 * Make a CMS model command
 *
 * @see ModelMakeCommand::getOptions()
 */
#[AsCommand('make:cms-model', 'Create a new CMS model class')]
class ModelMakeCommand extends GeneratorCommand
{
    use OperatesModulesDirectory;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->bootOperatesModulesDirectoryTrait();
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): ?bool
    {
        if ($this->option('all')) {
            $this->input->setOption('factory', true);
            $this->input->setOption('filter', true);
            $this->input->setOption('migration', true);
            $this->input->setOption('crud', true);
            $this->input->setOption('policy', true);
            $this->input->setOption('multilingual', true);
            $this->input->setOption('sort', true);
            $this->input->setOption('seed', true);
        } else {
            if ($this->confirm('Model is multilingual?', true)) {
                $this->input->setOption('multilingual', true);
            }
            if ($this->confirm('Need factory?', true)) {
                $this->input->setOption('factory', true);
            }
            if ($this->confirm('Need filter?', true)) {
                $this->input->setOption('filter', true);
            }
            if ($this->confirm('Need migration?', true)) {
                $this->input->setOption('migration', true);
            }
            if ($this->confirm('Need crud?', true)) {
                $this->input->setOption('crud', true);
                if ($this->confirm('Need site query?')) {
                    $this->input->setOption('site-query', true);
                }
            }
            if ($this->confirm('Need sort?')) {
                $this->input->setOption('sort', true);
            }
            // if ($this->confirm('Need policy?')) {
            //     $this->input->setOption('policy', true);
            // }
            // if ($this->confirm('Need seed?')) {
            //     $this->input->setOption('seed', true);
            // }
        }

        if (parent::handle() === false && !$this->option('force')) {
            return null;
        }

        if ($this->option('multilingual')) {
            $this->createTranslation();
        }

        if ($this->option('factory')) {
            $this->createFactory();
        }

        if ($this->option('filter')) {
            $this->createFilter();
        }

        if ($this->option('migration')) {
            $this->generateMigrations();
        }

        if ($this->option('crud')) {
            $this->createCrud();
        }

        if ($this->option('policy')) {
            // TODO
            //$this->createPolicy();
        }
        if ($this->option('seed')) {
            // TODO
            //$this->createSeeder();
        }

        return true;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $traits = collect([
            ActiveScopeTrait::class,
            $this->option('filter') ? Filterable::class : null,
            $this->option('factory') ? HasFactory::class : null,
            $this->option('multilingual') ? HasTranslations::class : null,
        ])->filter()->sort()->all();

        return $this->replaceDocBlock($stub)
            ->replaceNamespace($stub, $name)
            ->replaceTraits($stub, $traits)
            ->replaceAttributes($stub)
            ->replaceClass($stub, $name);
    }

    protected function createTranslation(): void
    {
        $class = $this->qualifyClass($this->argument('name') . 'Translation');

        $path = $this->getPath($class);

        if ($this->files->exists($path)) {
            $this->components->error($path . ' already exists.');

            return;
        }

        $traits = [
            $this->option('factory') ? HasFactory::class : null,
            TranslationTrait::class,
        ];

        $stub = file_get_contents(__DIR__ . '/stubs/model/model-translation.stub');
        $this->files->put(
            $path,
            $this->replaceDocBlock($stub)
                ->replaceNamespace($stub, $class)
                ->replaceTraits($stub, array_filter($traits))
                ->replaceClass($stub, $class)
        );

        $this->components->info(sprintf('%s [%s] created successfully.', 'Translation', $path));
    }

    /**
     * Create a model factory for the model.
     */
    protected function createFactory(): void
    {
        $factory = Str::studly($this->argument('name'));

        $this->call('make:cms-factory', [
            'name' => "{$factory}Factory",
            'module' => $this->argument('module'),
            '--model' => $this->qualifyClass($this->getNameInput()),
            '--multilingual' => $this->option('multilingual'),
            '--directory' => $this->option('directory'),
        ]);
    }

    /**
     * Create a model filter.
     */
    protected function createFilter(): void
    {
        $filter = Str::studly($this->argument('name'));

        $this->call('make:filter', [
            'name' => "{$filter}Filter",
            'module' => $this->argument('module'),
            '--multilingual' => $this->option('multilingual'),
            '--directory' => $this->option('directory'),
        ]);
    }

    protected function generateMigrations(): void
    {
        $table = (string)Str::of($this->argument('name'))->pluralStudly()->snake();

        $path = $this->modulePath('database/migrations');

        $this->makeDirectory($path);

        $this->createMigration($path, "create_{$table}_table");

        if ($this->option('multilingual')) {
            // Prevent from creating migration with same datetime
            sleep(1);

            $singularBaseTable = Str::singular($table);

            $migrationName = "create_{$singularBaseTable}_translations_table";

            $this->createMigration($path, $migrationName);

            $this->populateStub($path, $migrationName, Str::studly($this->argument('name')));
        }
    }

    protected function createMigration(string $path, string $name): void
    {
        if (count(glob("$path/*_$name.php")) === 0) {
            $this->call('make:migration', [
                'name' => $name,
                '--path' => str_after($path, base_path()),
            ]);
        }
    }

    /**
     * @param  class-string<Model>  $model
     *
     * @throws FileNotFoundException
     */
    protected function populateStub(string $migrationsPath, string $migration, string $model): void
    {
        $filePath = array_get($this->files->glob("$migrationsPath/*_$migration.php"), 0);
        if (!$filePath) {
            return;
        }

        $modelFullName = $this->qualifyClass($model);

        $content = $this->files->get($filePath);

        $content = str_replace(
            'use Illuminate\Support\Facades\Schema;',
            <<<PHP
use Illuminate\Support\Facades\Schema;
use $modelFullName;
PHP,
            $content
        );

        $content = str_replace(
            '$table->id();',
            <<<PHP
\$table->id();
            \$table->translationTo($model::class);

            \$table->string('name');
PHP,
            $content
        );

        $content = str_replace(
            <<<'PHP'
    $table->timestamps();
        });
PHP,
            '});',
            $content
        );

        $this->files->put($filePath, $content);
    }

    /**
     * Create a CRUD for the model.
     */
    protected function createCrud(): void
    {
        $name = Str::studly($this->argument('name'));

        $this->call('make:crud', [
            'name' => $name,
            'module' => $this->argument('module'),
            '--sort' => $this->option('sort'),
            '--multilingual' => $this->option('multilingual'),
            '--directory' => $this->option('directory'),
            '--site-query' => $this->option('site-query'),
        ]);
    }

    /**
     * Create a policy file for the model.
     */
    protected function createPolicy(): void
    {
        $policy = Str::studly(class_basename($this->argument('name')));

        $this->call('make:cms-policy', [
            'name' => "{$policy}Policy",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * Create a seeder file for the model.
     */
    protected function createSeeder(): void
    {
        $seeder = Str::studly(class_basename($this->argument('name')));

        $this->call('make:seeder', [
            'name' => "{$seeder}Seeder",
        ]);
    }

    protected function replaceTraits(string &$stub, array $traits): static
    {
        $stub = str_replace(
            '{{traits}}',
            implode(array_map(fn (string $class) => "    use \\$class;\n", $traits)),
            $stub
        );

        return $this;
    }

    protected function replaceAttributes(&$stub): static
    {
        $attributes = [
            'protected $fillable' => $this->option('sort') ? "['active', 'sort']" : "['active']",
        ];

        $stub = str_replace(
            '{{attributes}}',
            implode("\n", Arr::map($attributes, fn (string $value, string $index) => "    $index = $value;\n")),
            $stub
        );

        return $this;
    }

    protected function replaceDocBlock(&$stub): static
    {
        $stub = str_replace(
            '{{docblock}}',
            'TODO php artisan ide-helper:models -W "\DummyNamespace\DummyClass"',
            $stub
        );

        return $this;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->modulePath('src/Models/' . str_replace('\\', '/', $name) . '.php');
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/model/model.stub';
    }

    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        return 'Wezom\\' . Str::studly($this->argument('module')) . '\\Models\\';
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            [
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Generate multilingual sortable model, migration, seeder, factory, policy, CRUD',
            ],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['filter', 'F', InputOption::VALUE_NONE, 'Create a new model filter'],
            ['crud', 'c', InputOption::VALUE_NONE, 'Create a CRUD GraphQL types'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['migration', 'M', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            [
                'morph-pivot',
                null,
                InputOption::VALUE_NONE,
                'Indicates if the generated model should be a custom polymorphic intermediate table model',
            ],
            ['policy', null, InputOption::VALUE_NONE, 'Create a new policy for the model'],
            [
                'pivot',
                'p',
                InputOption::VALUE_NONE,
                'Indicates if the generated model should be a custom intermediate table model',
            ],
            ['multilingual', 'm', InputOption::VALUE_NONE, 'Model should be multilingual'],
            ['sort', 's', InputOption::VALUE_NONE, 'Model should be sortable'],
            ['seed', 'S', InputOption::VALUE_NONE, 'Create a new seeder for the model'],
            ['site-query', 'Q', InputOption::VALUE_NONE, 'Create site query'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if ($this->isReservedName($this->getNameInput()) || $this->didReceiveOptions($input)) {
            return;
        }

        collect(multiselect('Would you like any of the following?', [
            'factory' => 'Factory',
            'filter' => 'Model filter',
            'crud' => 'CRUD entry points',
            'migration' => 'Migration',
            'policy' => 'Policy',
        ]))->each(fn ($option) => $input->setOption($option, true));
    }
}
