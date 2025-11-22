<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians;

use App\Services\Technicians\TechnicianVerificationService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class TechnicianResendEmailVerificationMutation extends BaseMutation
{
    public const NAME = 'technicianResendEmailVerification';

    public function __construct(private TechnicianVerificationService $technicianVerificationService)
    {
        $this->setTechnicianGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->getAuthGuard()->check();
    }

    /** @throws Exception */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->technicianVerificationService->verifyEmail(
            $this->user()
        );
    }
}
