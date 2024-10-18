<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Closure;
use Fyre\Entity\Entity;
use Fyre\Error\Exceptions\ForbiddenException;
use Fyre\ORM\Model;

use function array_key_exists;
use function array_shift;
use function is_string;
use function method_exists;

/**
 * Access
 */
abstract class Access
{
    protected static array $afterRules = [];

    protected static array $beforeRules = [];

    protected static array $rules = [];

    /**
     * Execute a callback after checking rules.
     *
     * @param Closure $afterRule The callback.
     */
    public static function after(Closure $afterRule): void
    {
        static::$afterRules[] = $afterRule;
    }

    /**
     * Check whether an access rule is allowed.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool TRUE if the access rule was allowed, otherwise FALSE.
     */
    public static function allows(string $rule, mixed ...$args): bool
    {
        $user = Auth::instance()->user();

        $result = null;

        foreach (static::$beforeRules as $beforeRule) {
            $result ??= $beforeRule($user, $rule, ...$args);
        }

        if (array_key_exists($rule, static::$rules)) {
            $result ??= static::$rules[$rule]($user, ...$args);
        } else {
            $result ??= static::checkPolicy($rule, $args);
        }

        foreach (static::$afterRules as $afterRule) {
            $afterResult = $afterRule($user, $rule, $result, ...$args);
            $result ??= $afterResult;
        }

        return (bool) $result;
    }

    /**
     * Check whether any access rule is allowed.
     *
     * @param array $rules The access rule names.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool TRUE if any access rule was allowed, otherwise FALSE.
     */
    public static function any(array $rules, mixed ...$args): bool
    {
        foreach ($rules as $rule) {
            if (static::allows($rule, ...$args)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authorize an access rule.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     *
     * @throws ForbiddenException if access was not authorized.
     */
    public static function authorize(string $rule, mixed ...$args): void
    {
        if (!static::allows($rule, ...$args)) {
            throw new ForbiddenException();
        }
    }

    /**
     * Execute a callback before checking rules.
     *
     * @param Closure $beforeRule The callback.
     */
    public static function before(Closure $beforeRule): void
    {
        static::$beforeRules[] = $beforeRule;
    }

    /**
     * Clear all rules and callbacks.
     */
    public static function clear(): void
    {
        static::$afterRules = [];
        static::$beforeRules = [];
        static::$rules = [];
    }

    /**
     * Define an access rule.
     *
     * @param string $rule The access rule name.
     * @param Closure $callback The access rule callback.
     */
    public static function define(string $rule, Closure $callback): void
    {
        static::$rules[$rule] = $callback;
    }

    /**
     * Check whether an access rule is not allowed.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool TRUE if the access rule was not allowed, otherwise FALSE.
     */
    public static function denies(string $rule, mixed ...$args): bool
    {
        return !static::allows($rule, ...$args);
    }

    /**
     * Check whether no access rule is allowed.
     *
     * @param array $rules The access rule names.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool TRUE if no access rule was allowed, otherwise FALSE.
     */
    public static function none(array $rules, mixed ...$args): bool
    {
        return !static::any($rules, ...$args);
    }

    /**
     * Check a Policy rule.
     *
     * @param string $rule The Policy rule name.
     * @param mixed $args Additional arguments for the Policy rule.
     * @return bool|null TRUE if the Policy rule was passed, FALSE if it failed, otherwise NULL.
     */
    protected static function checkPolicy(string $rule, array $args): bool|null
    {
        $value = array_shift($args);
        $item = null;

        if (is_string($value)) {
            $alias = $value;
        } else if ($value instanceof Entity) {
            $item = $value;
            $alias = $item->getSource();
        } else if ($value instanceof Model) {
            $alias = $value->getAlias();
        } else {
            return null;
        }

        $policy = PolicyRegistry::use($alias);

        if (!$policy || !method_exists($policy, $rule)) {
            return null;
        }

        if ($args !== []) {
            $item ??= $policy->resolveEntity($args);
        }

        $user = Auth::instance()->user();

        return $policy->$rule($user, $item);
    }
}
