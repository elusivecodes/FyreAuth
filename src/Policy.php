<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\Entity\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;

/**
 * Policy
 */
abstract class Policy
{
    protected string $alias;

    protected Model $model;

    /**
     * New Policy constructor.
     */
    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    /**
     * Get the Model.
     *
     * @return Model The Model.
     */
    public function getModel(): Model
    {
        return $this->model ??= ModelRegistry::use($this->alias);
    }

    /**
     * Resolve an Entity from access rule arguments.
     *
     * @param array $args The access rule arguments.
     * @return Entity|null The Entity.
     */
    public function resolveEntity(array $args): Entity|null
    {
        return $this->getModel()->get($args);
    }
}
