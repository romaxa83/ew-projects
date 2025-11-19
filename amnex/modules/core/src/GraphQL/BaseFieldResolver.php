<?php

namespace Wezom\Core\GraphQL;

use BackedEnum;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Exceptions\AuthorizationException as LighthouseAuthorizationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Stringable;
use Throwable;
use UnitEnum;
use Wezom\Core\Exceptions\Auth\PermissionNotRegisteredException;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\ExtendPackage\Exception\ValidationException;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Services\AbilityCheckerService;
use Wezom\Core\Services\AuthenticationService;
use Wezom\Core\Utils\ResponseMessageHelper;
use Wezom\Core\Validation\SimpleValidator;

/**
 * @template DTO of Data
 */
abstract class BaseFieldResolver
{
    /**
     * Execute resolve() method in transaction.
     */
    protected bool $runInTransaction = false;

    /**
     * Generate ResponseMessage response.
     */
    protected bool $toResponseMessage = false;

    /**
     * Specifies the relationship between the input name and the Dto (Spatie\Data) for validation.
     *
     * @var array<string, class-string<DTO>>
     */
    protected array $dtoRulesMap = [];

    /**
     * @var class-string<DTO>
     */
    protected string $inputDto;

    protected string $inputName = 'input';

    abstract public function resolve(Context $context);

    /**
     * @throws Throwable
     */
    public function __invoke(
        mixed $root,
        array $args,
        ?GraphQLContext $graphQlContext,
        ResolveInfo $resolveInfo
    ): mixed {
        try {
            $args = $this->initArgs($args);

            $context = new Context(
                $root,
                $args,
                $graphQlContext,
                $resolveInfo
            );

            $this->authenticate($context);
            $this->authorize($context);

            $this->validate($args);

            return $this->processResolve($context);
        } catch (TranslatedException|AuthenticationException|AuthorizationException|ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            if (is_prod()) {
                logger($e);

                throw new TranslatedException(__('exceptions.default'));
            }

            throw $e;
        }
    }

    protected function initArgs(array $args): array
    {
        return $args;
    }

    /**
     * @throws AuthenticationException
     */
    protected function authenticate(Context $context): void
    {
        if (!$guards = $this->guards()) {
            return;
        }

        app(AuthenticationService::class)->authenticate($guards, $context->getGraphQlContext());
    }

    protected function guards(): array|string
    {
        return [];
    }

    /**
     * @throws LighthouseAuthorizationException
     * @throws PermissionNotRegisteredException
     */
    protected function authorize(Context $context): void
    {
        if (!$ability = $this->ability()) {
            return;
        }

        if ($ability instanceof UnitEnum) {
            $ability = enum_to_string($ability);
        }

        $user = $context->getGraphQlContext()->user();
        $abilities = array_wrap($ability);
        $ids = $context->getArg($this->abilityIdArgument());

        app(AbilityCheckerService::class)->inspect($user, $abilities, $ids);
    }

    protected function ability(): Ability|Stringable|UnitEnum|BackedEnum|string|array|null
    {
        return null;
    }

    protected function abilityIdArgument(): string
    {
        return 'id';
    }

    /**
     * @throws ValidationException
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    protected function validate(array $args): void
    {
        $messages = [
            ...$this->validateRules($args),
            ...$this->validateDto($args),
        ];

        if (!$messages) {
            return;
        }

        throw new ValidationException('validation', new SimpleValidator($messages));
    }

    private function validateRules(array $args): array
    {
        try {
            $validator = Validator::make(
                $args,
                $this->rules($args),
                $this->messages(),
                $this->attributes(),
            );

            $validator->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $e->validator->errors()->messages();
        }

        return [];
    }

    private function validateDto(array $args): array
    {
        $errors = [];
        foreach ($this->dtoRulesMap() as $field => $dto) {
            /** @var class-string<Data> $dto */
            try {
                $payload = $args[$field] ?? [];
                if ($id = $args['id'] ?? null) {
                    $payload['id'] = $id;
                }

                $dto::validate($payload);
            } catch (\Illuminate\Validation\ValidationException $e) {
                foreach ($e->validator->errors()->messages() as $path => $error) {
                    $errors[$field . '.' . $path] = $error;
                }
            }
        }

        return $errors;
    }

    protected function dtoRulesMap(): array
    {
        if (empty($this->dtoRulesMap) && isset($this->inputDto)) {
            $this->dtoRulesMap[$this->inputName] = $this->inputDto;
        }

        return $this->dtoRulesMap;
    }

    /**
     * @template MODEL of Model
     *
     * @param class-string<MODEL> $model
     */
    protected function idRule(string $model): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists($model, 'id')],
        ];
    }

    /** @throws Throwable */
    protected function processResolve(Context $context): mixed
    {
        $runInTransaction = $this->runInTransaction ?? false;
        if ($this->toResponseMessage ?? false) {
            return ResponseMessageHelper::make()
                ->inTransaction($runInTransaction)
                ->execute(fn () => $this->callResolve($context));
        }

        if ($runInTransaction) {
            return make_transaction(fn () => $this->callResolve($context));
        }

        return $this->callResolve($context);
    }

    private function callResolve(Context $context): mixed
    {
        return app()->call([$this, 'resolve'], ['context' => $context]);
    }

    protected function rules(array $args = []): array
    {
        return [];
    }

    protected function messages(): array
    {
        return [];
    }

    protected function attributes(): array
    {
        return [];
    }

    /**
     * @deprecated 1.0 Use $dtoRulesMap or $inputDto
     *
     * @see BaseFieldResolver::$dtoRulesMap
     * @see BaseFieldResolver::$inputDto
     */
    protected function getDtoValidationRules(
        string $class,
        array $args,
        ?string $path = null,
        string $idKey = 'id'
    ): array {
        if ($path) {
            $payload = $args[$path];
            $payload['id'] = $args[$idKey] ?? null;
        } else {
            $payload = $args;
        }

        return app(DataValidationRulesResolver::class)
            ->execute(
                $class,
                $payload,
                ValidationPath::create($path),
                DataRules::create(),
            );
    }

    public static function getName(): string
    {
        return str(get_called_class())->classBasename()->camel();
    }
}
