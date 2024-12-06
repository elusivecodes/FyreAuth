<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Closure;
use Fyre\Entity\Entity;
use Fyre\Error\Exceptions\ForbiddenException;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\Utility\Inflector;
use ReflectionClass;
use ReflectionFunction;

use function array_key_exists;
use function array_shift;
use function count;
use function is_string;
use function method_exists;

/**
 * Access
 */
class Access
{
    protected array $afterRules = [];

    protected array $beforeRules = [];

    protected Inflector $inflector;

    protected ModelRegistry $modelRegistry;

    protected PolicyRegistry $policyRegistry;

    protected array $rules = [];

    protected Closure $userResolver;

    /**
     * New Access constructor.
     *
     * @param Closure $userResolver The user resolver.
     * @param Inflector $inflector The Inflector.
     * @param PolicyRegistry $policyRegistry The PolicyRegistry.
     * @param ModelRegistry $modelRegistry The ModelRegistry.
     */
    public function __construct(Closure $userResolver, Inflector $inflector, PolicyRegistry $policyRegistry, ModelRegistry $modelRegistry)
    {
        $this->userResolver = $userResolver;
        $this->inflector = $inflector;
        $this->policyRegistry = $policyRegistry;
        $this->modelRegistry = $modelRegistry;
    }

    /**
     * Execute a callback after checking rules.
     *
     * @param Closure $afterRule The callback.
     */
    public function after(Closure $afterRule): void
    {
        $this->afterRules[] = $afterRule;
    }

    /**
     * Check whether an access rule is allowed.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool TRUE if the access rule was allowed, otherwise FALSE.
     */
    public function allows(string $rule, mixed ...$args): bool
    {
        $user = ($this->userResolver)();

        $result = null;

        foreach ($this->beforeRules as $beforeRule) {
            if (!$user && static::isClosureUserRequired($beforeRule)) {
                continue;
            }

            $result ??= $beforeRule($user, $rule, ...$args);
        }

        $result ??= $this->checkRule($rule, $args);
        $result ??= $this->checkPolicy($rule, $args);

        foreach ($this->afterRules as $afterRule) {
            if (!$user && static::isClosureUserRequired($afterRule)) {
                continue;
            }

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
    public function any(array $rules, mixed ...$args): bool
    {
        foreach ($rules as $rule) {
            if ($this->allows($rule, ...$args)) {
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
    public function authorize(string $rule, mixed ...$args): void
    {
        if (!$this->allows($rule, ...$args)) {
            throw new ForbiddenException();
        }
    }

    /**
     * Execute a callback before checking rules.
     *
     * @param Closure $beforeRule The callback.
     */
    public function before(Closure $beforeRule): void
    {
        $this->beforeRules[] = $beforeRule;
    }

    /**
     * Clear all rules and callbacks.
     */
    public function clear(): void
    {
        $this->afterRules = [];
        $this->beforeRules = [];
        $this->rules = [];
    }

    /**
     * Define an access rule.
     *
     * @param string $rule The access rule name.
     * @param Closure $callback The access rule callback.
     */
    public function define(string $rule, Closure $callback): void
    {
        $this->rules[$rule] = $callback;
    }

    /**
     * Check whether an access rule is not allowed.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool TRUE if the access rule was not allowed, otherwise FALSE.
     */
    public function denies(string $rule, mixed ...$args): bool
    {
        return !$this->allows($rule, ...$args);
    }

    /**
     * Check whether no access rule is allowed.
     *
     * @param array $rules The access rule names.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool TRUE if no access rule was allowed, otherwise FALSE.
     */
    public function none(array $rules, mixed ...$args): bool
    {
        return !$this->any($rules, ...$args);
    }

    /**
     * Check a Policy rule.
     *
     * @param string $rule The Policy rule name.
     * @param mixed $args Additional arguments for the Policy rule.
     * @return bool|null TRUE if the Policy rule was passed, FALSE if it failed, otherwise NULL.
     */
    protected function checkPolicy(string $rule, array $args): bool|null
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

        $policy = $this->policyRegistry->use($alias);
        $method = $this->inflector->variable($rule);

        if (!$policy || !method_exists($policy, $method)) {
            return null;
        }

        if ($args !== [] && $item === null) {
            $alias = $this->policyRegistry->resolveAlias($alias);
            $item = $this->modelRegistry->use($alias)->get($args);
        }

        $user = ($this->userResolver)();

        if (!$user || !$item) {
            $params = (new ReflectionClass($policy))
                ->getMethod($method)
                ->getParameters();

            if (!$user && !$params !== [] && !$params[0]->allowsNull()) {
                return false;
            }

            if (!$item && count($params) > 1 && !$params[1]->allowsNull()) {
                return false;
            }
        }

        return $policy->$method($user, $item);
    }

    /**
     * Check an access rule.
     *
     * @param string $rule The access rule name.
     * @param mixed $args Additional arguments for the access rule.
     * @return bool|null TRUE if the access rule was passed, FALSE if it failed, otherwise NULL.
     */
    protected function checkRule(string $rule, array $args): bool|null
    {
        if (!array_key_exists($rule, $this->rules)) {
            return null;
        }

        $user = ($this->userResolver)();

        if (!$user && static::isClosureUserRequired($this->rules[$rule])) {
            return false;
        }

        return $this->rules[$rule]($user, ...$args);
    }

    /**
     * Determine if the user parameter is required for a Closure.
     *
     * @param Closure $callback The Closure.
     * @return bool TRUE if the user parameter is required, otherwise FALSE.
     */
    protected static function isClosureUserRequired(Closure $callback): bool
    {
        $params = (new ReflectionFunction($callback))->getParameters();

        return $params !== [] && !$params[0]->allowsNull();
    }
}
