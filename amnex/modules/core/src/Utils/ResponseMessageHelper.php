<?php

declare(strict_types=1);

namespace Wezom\Core\Utils;

use Exception;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Wezom\Core\Entities\Messages\ResponseMessageEntity;
use Wezom\Core\Exceptions\TranslatedException;

class ResponseMessageHelper
{
    private function __construct(
        private bool $inTransaction = true,
        private string $successMessage = 'core::messages.action.success',
        private string $failMessage = 'core::messages.action.fail',
    ) {
    }

    public static function make(): ResponseMessageHelper
    {
        return new ResponseMessageHelper();
    }

    public function successMessage(string $messageKey): ResponseMessageHelper
    {
        $this->successMessage = $messageKey;

        return $this;
    }

    public function failMessage(string $messageKey): ResponseMessageHelper
    {
        $this->failMessage = $messageKey;

        return $this;
    }

    public function inTransaction(bool $inTransaction): ResponseMessageHelper
    {
        $this->inTransaction = $inTransaction;

        return $this;
    }

    public function withoutTransaction(): ResponseMessageHelper
    {
        return $this->inTransaction(false);
    }

    public function execute(callable $callback): array|ResponseMessageEntity
    {
        try {
            $result = $this->inTransaction ? make_transaction($callback) : $callback();

            if ($result instanceof ResponseMessageEntity) {
                return $result;
            }

            return [ResponseMessageEntity::success(__($this->successMessage))];
        } catch (AuthorizationException|TranslatedException $e) {
            throw $e;
        } catch (Exception) {
            return [ResponseMessageEntity::fail(__($this->failMessage))];
        }
    }
}
