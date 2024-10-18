<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\Entity\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;

use function password_hash;
use function password_needs_rehash;
use function password_verify;

use const PASSWORD_DEFAULT;

/**
 * Identity
 */
abstract class Identity
{
    protected static array $identifierFields = ['email'];

    protected static Model $model;

    protected static string $passwordField = 'password';

    /**
     * Attempt to identify a user.
     *
     * @param string $identifier The user identifier.
     * @param string $password The user password.
     * @return Entity|null The identifier user.
     */
    public static function attempt(string $identifier, string $password): Entity|null
    {
        if (!$identifier || !$password) {
            return null;
        }

        $user = static::identify($identifier);

        if (!$user) {
            return null;
        }

        $passwordField = static::getPasswordField();
        $passwordHash = $user->get($passwordField);

        if (!password_verify($password, $passwordHash)) {
            return null;
        }

        if (password_needs_rehash($passwordHash, PASSWORD_DEFAULT)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $user->set($passwordField, $passwordHash);

            $Model = static::getModel();

            $primaryKey = $Model->getPrimaryKey();
            $primaryValues = $user->extract($primaryKey);

            $Model->updateAll([
                $passwordField => $passwordHash,
            ], $primaryValues);
        }

        return $user;
    }

    /**
     * Get the user identifier fields.
     *
     * @return array The user identifier fields.
     */
    public static function getIdentifierFields(): array
    {
        return static::$identifierFields;
    }

    /**
     * Get the identity Model.
     *
     * @return Model The identity Model.
     */
    public static function getModel(): Model
    {
        return static::$model ??= ModelRegistry::use('Users');
    }

    /**
     * Get the user password field.
     *
     * @return string The user password field.
     */
    public static function getPasswordField(): string
    {
        return static::$passwordField;
    }

    /**
     * Find an identity by identifier.
     *
     * @param string $identifier The identifier.
     * @return Entity|null The Entity.
     */
    public static function identify(string $identifier): Entity|null
    {
        $Model = static::getModel();

        $orConditions = [];

        foreach (static::$identifierFields as $identifierField) {
            $orConditions[$Model->aliasField($identifierField)] = $identifier;
        }

        return $Model->find()
            ->where([
                'or' => $orConditions,
            ])
            ->first();
    }

    /**
     * Set the identifier fields.
     *
     * @param array $identifierFields The identifier fields.
     */
    public static function setIdentifierFields($identifierFields): void
    {
        static::$identifierFields = $identifierFields;
    }

    /**
     * Set the identity Model.
     *
     * @param Model $model The identity Model.
     */
    public static function setModel(Model $model): void
    {
        static::$model = $model;
    }

    /**
     * Set the password field.
     *
     * @param string $passwordField The password field.
     */
    public static function setPasswordField(string $passwordField): void
    {
        static::$passwordField = $passwordField;
    }
}
