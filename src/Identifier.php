<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Closure;
use Fyre\Config\Config;
use Fyre\Entity\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;

use function array_replace;
use function count;
use function password_hash;
use function password_needs_rehash;
use function password_verify;

use const PASSWORD_DEFAULT;

/**
 * Identifier
 */
class Identifier
{
    protected static array $defaults = [
        'identifierFields' => ['email'],
        'passwordField' => 'password',
        'modelAlias' => 'Users',
        'queryCallback' => null,
    ];

    protected array $identifierFields;

    protected Model $model;

    protected string $passwordField;

    protected Closure|null $queryCallback;

    /**
     * New Identifier constructor.
     *
     * @param Config $config The Config.
     * @param ModelRegistry $modelRegistry The ModelRegistry.
     */
    public function __construct(Config $config, ModelRegistry $modelRegistry)
    {
        $options = array_replace(static::$defaults, $config->get('Auth.identifier', []));

        $this->model = $modelRegistry->use($options['modelAlias']);

        $this->identifierFields = (array) $options['identifierFields'];
        $this->passwordField = $options['passwordField'];
        $this->queryCallback = $options['queryCallback'];
    }

    /**
     * Attempt to identify a user.
     *
     * @param string $identifier The user identifier.
     * @param string $password The user password.
     * @return Entity|null The identifier user.
     */
    public function attempt(string $identifier, string $password): Entity|null
    {
        if (!$identifier || !$password) {
            return null;
        }

        $user = $this->identify($identifier);

        if (!$user) {
            return null;
        }

        $passwordField = $this->getPasswordField();
        $passwordHash = $user->get($passwordField);

        if (!password_verify($password, $passwordHash)) {
            return null;
        }

        if (password_needs_rehash($passwordHash, PASSWORD_DEFAULT)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $user->set($passwordField, $passwordHash);

            $Model = $this->getModel();

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
    public function getIdentifierFields(): array
    {
        return $this->identifierFields;
    }

    /**
     * Get the identity Model.
     *
     * @return Model The identity Model.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Get the user password field.
     *
     * @return string The user password field.
     */
    public function getPasswordField(): string
    {
        return $this->passwordField;
    }

    /**
     * Find an identity by identifier.
     *
     * @param string $identifier The identifier.
     * @return Entity|null The Entity.
     */
    public function identify(string $identifier): Entity|null
    {
        $Model = $this->getModel();

        $orConditions = [];

        foreach ($this->identifierFields as $identifierField) {
            $orConditions[$Model->aliasField($identifierField)] = $identifier;
        }

        $query = $Model->find();

        if (count($orConditions) > 1) {
            $query->where(['or' => $orConditions]);
        } else {
            $query->where($orConditions);
        }

        if ($this->queryCallback) {
            $query = ($this->queryCallback)($query);
        }

        return $query->first();
    }
}
