<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\ORM\Model;
use Fyre\Utility\Inflector;

use function array_key_exists;
use function array_splice;
use function class_exists;
use function in_array;
use function is_subclass_of;
use function trim;

/**
 * PolicyRegistry
 */
abstract class PolicyRegistry
{
    protected static array $instances = [];

    protected static array $namespaces = [];

    protected static array $policyMap = [];

    /**
     * Add a namespace for loading policies.
     *
     * @param string $namespace The namespace.
     */
    public static function addNamespace(string $namespace): void
    {
        $namespace = static::normalizeNamespace($namespace);

        if (!in_array($namespace, static::$namespaces)) {
            static::$namespaces[] = $namespace;
        }
    }

    /**
     * Clear all namespaces and policies.
     */
    public static function clear(): void
    {
        static::$policyMap = [];
        static::$namespaces = [];
        static::$instances = [];
    }

    /**
     * Get the namespaces.
     *
     * @return array The namespaces.
     */
    public static function getNamespaces(): array
    {
        return static::$namespaces;
    }

    /**
     * Determine if a namespace exists.
     *
     * @param string $namespace The namespace.
     * @return bool TRUE if the namespace exists, otherwise FALSE.
     */
    public static function hasNamespace(string $namespace): bool
    {
        $namespace = static::normalizeNamespace($namespace);

        return in_array($namespace, static::$namespaces);
    }

    /**
     * Load a Policy.
     *
     * @param string $alias The policy alias.
     * @return Policy|null The Policy.
     */
    public static function load(string $alias): Policy|null
    {
        $alias = static::resolveAlias($alias);

        if (array_key_exists($alias, static::$policyMap)) {
            $fullClass = static::$policyMap[$alias];

            return new $fullClass($alias);
        }

        $singular = Inflector::singularize($alias);

        foreach (static::$namespaces as $namespace) {
            $fullClass = $namespace.$singular.'Policy';

            if (class_exists($fullClass) && is_subclass_of($fullClass, Policy::class)) {
                return new $fullClass($alias);
            }
        }

        return null;
    }

    /**
     * Map an alias to a policy class name.
     *
     * @param string $alias The policy alias.
     * @param string $className The policy class name.
     */
    public static function map(string $alias, string $className): void
    {
        $alias = static::resolveAlias($alias);

        static::$policyMap[$alias] = $className;
    }

    /**
     * Remove a namespace.
     *
     * @param string $namespace The namespace.
     * @return bool TRUE If the namespace was removed, otherwise FALSE.
     */
    public static function removeNamespace(string $namespace): bool
    {
        $namespace = static::normalizeNamespace($namespace);

        foreach (static::$namespaces as $i => $otherNamespace) {
            if ($otherNamespace !== $namespace) {
                continue;
            }

            array_splice(static::$namespaces, $i, 1);

            return true;
        }

        return false;
    }

    /**
     * Unload a policy.
     *
     * @param string $alias The policy alias.
     * @return bool TRUE if the policy was removed, otherwise FALSE.
     */
    public static function unload(string $alias): bool
    {
        $alias = static::resolveAlias($alias);

        if (!array_key_exists($alias, static::$instances)) {
            return false;
        }

        unset(static::$instances[$alias]);

        return true;
    }

    /**
     * Load a shared policy instance.
     *
     * @param string $alias The alias.
     * @return Policy|null The policy class name.
     */
    public static function use(string $alias): Policy|null
    {
        $alias = static::resolveAlias($alias);

        if (array_key_exists($alias, static::$instances)) {
            return static::$instances[$alias];
        }

        return static::$instances[$alias] = static::load($alias);
    }

    /**
     * Normalize a namespace
     *
     * @param string $namespace The namespace.
     * @return string The normalized namespace.
     */
    protected static function normalizeNamespace(string $namespace): string
    {
        $namespace = trim($namespace, '\\');

        return $namespace ?
            '\\'.$namespace.'\\' :
            '\\';
    }

    /**
     * Resolve a modal alias.
     *
     * @param string $alias The modal alias.
     * @return string The resolved alias.
     */
    protected static function resolveAlias(string $alias): string
    {
        if (class_exists($alias) && is_a($alias, Model::class, true)) {
            return (new $alias())->getAlias();
        }

        return $alias;
    }
}
