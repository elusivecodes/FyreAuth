<?php
declare(strict_types=1);

namespace Fyre\Auth\Exceptions;

use RuntimeException;

/**
 * AuthException
 */
class AuthException extends RuntimeException
{
    public static function forInvalidAuthenticatorClass(string $className = ''): static
    {
        return new static('Authenticator class not found: '.$className);
    }
}
