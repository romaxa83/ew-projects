<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Symfony\Component\VarDumper\VarDumper;
use Wezom\Core\Testing\QueryBuilder\UnionValue;

abstract class Projection
{
    /**
     * Indicates that current projection name should be plural
     */
    protected bool $asCollection = false;

    /**
     * Base projection name. Can be overrides by 'alias' param in root() or collection() methods
     *
     * @see Projection::root()
     * @see Projection::collection()
     */
    protected ?string $rootName = null;

    /**
     * Wrap projection into 'data'
     *
     * @var bool
     */
    private bool $paginationMode = false;

    private array $fields = [];

    private function __construct(protected ?string $alias = null)
    {
    }

    protected static function newInstance(?string $alias = null, bool $paginate = false): static
    {
        $projection = new static($alias);

        $projection->paginationMode = $paginate;

        $projection->addFields($projection->fields());

        return $projection;
    }

    abstract protected function fields(): array;

    protected function addFields(array|Projection $fields): void
    {
        if ($this->paginationMode) {
            $fields = ['data' => $fields];
        }

        $this->fields = array_merge($this->fields, Arr::wrap($fields));
    }

    public static function root(?string $alias = null): static
    {
        return static::newInstance($alias);
    }

    public static function collection(?string $alias = null): static
    {
        return static::newInstance($alias)->asCollection();
    }

    protected function asCollection(): static
    {
        $this->asCollection = true;

        return $this;
    }

    public static function union(): array
    {
        return static::newInstance()->asUnion();
    }

    /** @return array<string, UnionValue> */
    protected function asUnion(): array
    {
        $type = $this->resolveProjectionName()->studly()->toString();

        return [
            $type => new UnionValue(self::root()->get())
        ];
    }

    public static function pagination(?string $alias = null): static
    {
        return static::newInstance($alias, true);
    }

    public function withTranslation(): static
    {
        return $this->with($this->resolveTranslationProjection());
    }

    public function withTranslations(): static
    {
        return $this->with($this->resolveTranslationProjection()->asCollection());
    }

    public function withFile(string $name): static
    {
        return $this->with(FileProjection::root($name));
    }

    /**
     * Usage:
     * <p>Example1</p>
     * <pre>
     * ->withIdProjection('some_name')
     * //Generates array with only id field:
     * [
     *     'some_name' => [
     *         'id',
     *     ]
     * ]
     * </pre>
     * <p>Example2. With any additional projections:</p>
     * <pre>
     * ->withIdProjection(
     *      'some_name',
     *      Any1Projection::root(),
     *      Any2Projection::root()->withTranslation()
     * )
     * //Generates the next structure:
     * [
     *      'some_name' => [
     *          'id',
     *          'any1' => [
     *               //Any1Projection fields
     *          ],
     *          'any2' => [
     *                //Any2Projection fields
     *               'translation' => [
     *                   //Any2TranslationProjection fields
     *               ]
     *           ],
     *      ]
     *  ]
     * </pre>
     *
     * @param string $name
     * @param Projection ...$with List of projections to be added inside "$name" Projection
     *
     * @return static
     */
    public function withIdProjection(string $name, Projection ...$with): static
    {
        $idProjection = IdProjection::root($name);

        foreach ($with as $withProjection) {
            $idProjection->with($withProjection);
        }

        return $this->with($idProjection);
    }

    protected function resolveTranslationProjection(): Projection
    {
        $translation = Str::of(static::class)
            ->replaceLast('Projection', 'TranslationProjection')
            ->value();

        if (class_exists($translation)) {
            return $translation::root();
        }

        throw new TranslationProjectionNotFoundException(
            sprintf(
                'Translation projection class "%s" does not exists for projection "%s"',
                class_basename($translation),
                static::class,
            )
        );
    }

    public function with(Projection $projection): static
    {
        $name = $projection->getProjectionName();

        if ($this->paginationMode) {
            $this->fields['data'][$name] = $projection;
        } else {
            $this->fields[$name] = $projection;
        }

        return $this;
    }

    protected function getProjectionName(): string
    {
        if (!empty($this->alias)) {
            return $this->alias;
        }

        $projection = $this->resolveProjectionName();

        if ($this->asCollection) {
            $projection = $projection->plural();
        }

        return $projection->value();
    }

    protected function resolveProjectionName(): Stringable
    {
        if ($this->rootName) {
            return Str::of($this->rootName);
        }

        return Str::of(get_called_class())
            ->classBasename()
            ->remove(['Projection'])
            ->camel();
    }

    public function get(): array
    {
        return $this->projectionToArray($this->fields);
    }

    private function projectionToArray(array $projection): array
    {
        foreach ($projection as $key => $value) {
            $projection[$key] = is_array($value) ? $this->projectionToArray($value) : $value;

            if ($value instanceof Projection) {
                $projection[$key] = $value->get();
            }
        }

        return $projection;
    }

    /**
     * @return never-return
     */
    public function dd(): void
    {
        $this->dump();

        exit(1);
    }

    public function dump(): static
    {
        VarDumper::dump($this->get());

        return $this;
    }
}
