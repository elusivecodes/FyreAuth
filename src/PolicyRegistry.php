<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\Container\Container;
use Fyre\ORM\Model;
use Fyre\Utility\Inflector;
use ReflectionClass;

use function array_key_exists;
use function array_splice;
use function class_exists;
use function in_array;
use function is_subclass_of;
use function preg_replace;
use function trim;

/**
 * PolicyRegistry
 */
class PolicyRegistry
{
    protected array $aliases = [];

    protected array $instances = [];

    protected array $namespaces = [];

    protected array $policyMap = [];

    /**
     * New PolicyRegistry constructor.
     *
     * @param Container $container The Container.
     * @param Inflector $inflector The Inflector.
     */
    public function __construct(
        protected Container $container,
        protected Inflector $inflector
    ) {}

    /**
     * Add a namespace for loading policies.
     *
     * @param string $namespace The namespace.
     * @return PolicyRegistry The PolicyRegistry.
     */
    public function addNamespace(string $namespace): static
    {
        $namespace = static::normalizeNamespace($namespace);

        if (!in_array($namespace, $this->namespaces)) {
            $this->namespaces[] = $namespace;
        }

        return $this;
    }

    /**
     * Build a Policy.
     *
     * @param string $alias The policy alias.
     * @return object|null The Policy.
     */
    public function build(string $alias): object|null
    {
        $alias = $this->resolveAlias($alias);

        if (array_key_exists($alias, $this->policyMap)) {
            return $this->container->build($this->policyMap[$alias]);
        }

        $singular = $this->inflector->singularize($alias);

        foreach ($this->namespaces as $namespace) {
            $fullClass = $namespace.$singular.'Policy';

            if (class_exists($fullClass)) {
                return $this->container->build($fullClass);
            }
        }

        return null;
    }

    /**
     * Clear all namespaces and policies.
     */
    public function clear(): void
    {
        $this->aliases = [];
        $this->policyMap = [];
        $this->namespaces = [];
        $this->instances = [];
    }

    /**
     * Get the namespaces.
     *
     * @return array The namespaces.
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Determine if a namespace exists.
     *
     * @param string $namespace The namespace.
     * @return bool TRUE if the namespace exists, otherwise FALSE.
     */
    public function hasNamespace(string $namespace): bool
    {
        $namespace = static::normalizeNamespace($namespace);

        return in_array($namespace, $this->namespaces);
    }

    /**
     * Map an alias to a policy class name.
     *
     * @param string $alias The policy alias.
     * @param string $className The policy class name.
     * @return PolicyRegistry The PolicyRegistry.
     */
    public function map(string $alias, string $className): static
    {
        $alias = $this->resolveAlias($alias);

        $this->policyMap[$alias] = $className;

        return $this;
    }

    /**
     * Remove a namespace.
     *
     * @param string $namespace The namespace.
     * @return PolicyRegistry The PolicyRegistry.
     */
    public function removeNamespace(string $namespace): static
    {
        $namespace = static::normalizeNamespace($namespace);

        foreach ($this->namespaces as $i => $otherNamespace) {
            if ($otherNamespace !== $namespace) {
                continue;
            }

            array_splice($this->namespaces, $i, 1);
            break;
        }

        return $this;
    }

    /**
     * Resolve a model alias.
     *
     * @param string $alias The model alias.
     * @return string The resolved alias.
     */
    public function resolveAlias(string $alias): string
    {
        if (array_key_exists($alias, $this->aliases)) {
            return $this->aliases[$alias];
        }

        if (class_exists($alias) && is_subclass_of($alias, Model::class)) {
            $reflect = new ReflectionClass($alias);
            $alias = $reflect->getProperty('alias')->getDefaultValue() ?: preg_replace('/Model$/', '', $reflect->getShortName());
        }

        return $this->aliases[$alias] = $alias;
    }

    /**
     * Unload a policy.
     *
     * @param string $alias The policy alias.
     * @return PolicyRegistry The PolicyRegistry.
     */
    public function unload(string $alias): static
    {
        unset($this->instances[$alias]);

        return $this;
    }

    /**
     * Load a shared policy instance.
     *
     * @param string $alias The alias.
     * @return object|null The policy class name.
     */
    public function use(string $alias): object|null
    {
        $alias = $this->resolveAlias($alias);

        return $this->instances[$alias] ??= $this->build($alias);
    }

    /**
     * Normalize a namespace
     *
     * @param string $namespace The namespace.
     * @return string The normalized namespace.
     */
    protected static function normalizeNamespace(string $namespace): string
    {
        return trim($namespace, '\\').'\\';
    }
}
