<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Closure;
use Fyre\Entity\Entity;

use function get_class;

/**
 * Auth
 */
class Auth
{
    protected static Auth|Closure|null $instance = null;

    protected array $authenticators = [];

    protected Entity|null $user = null;

    /**
     * Load a shared Auth instance.
     *
     * @return Auth The Auth.
     */
    public static function instance(): static
    {
        if (static::$instance && static::$instance instanceof Closure) {
            return (static::$instance)();
        }

        return static::$instance ??= new static();
    }

    /**
     * Set a shared Auth instance.
     *
     * @param Auth|Closure $instance The Auth, or a callback that returns the Auth.
     */
    public static function setInstance(Auth|Closure $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * Add an Authenticator.
     *
     * @param Authenticator $authenticator The Authenticator.
     * @param string|null $key The key.
     * @return static The Auth.
     */
    public function addAuthenticator(Authenticator $authenticator, string|null $key = null)
    {
        $key ??= get_class($authenticator);

        $this->authenticators[$key] = $authenticator;

        return $this;
    }

    /**
     * Attempt to login as a user.
     *
     * @param string $identifier The user identifier.
     * @param string $password The user password.
     * @param bool $rememberMe Whether to remember the user.
     * @return Entity|null The logged in user.
     */
    public function attempt(string $identifier, string $password, bool $rememberMe = false): Entity|null
    {
        $user = Identity::attempt($identifier, $password);

        if (!$user) {
            return null;
        }

        static::login($user, $rememberMe);

        return $user;
    }

    /**
     * Get an authenticator by key.
     *
     * @param string $key The key.
     * @return Authenticator The Authenticator.
     */
    public function authenticator(string $key): Authenticator|null
    {
        return $this->authenticators[$key] ?? null;
    }

    /**
     * Get the authenticators.
     *
     * @return array The authenticators.
     */
    public function authenticators(): array
    {
        return $this->authenticators;
    }

    /**
     * Determine if the current user is logged in.
     *
     * @return bool TRUE if the current user is logged in, otherwise FALSE.
     */
    public function isLoggedIn(): bool
    {
        return (bool) $this->user;
    }

    /**
     * Login as a user.
     *
     * @param Entity $user The user.
     * @param bool $rememberMe Whether to remember the user.
     * @return static The Auth.
     */
    public function login(Entity $user, bool $rememberMe = false): static
    {
        $this->user = $user;

        foreach ($this->authenticators as $authenticator) {
            $authenticator->login($user, $rememberMe);
        }

        return $this;
    }

    /**
     * Logout the current user.
     *
     * @return static The Auth.
     */
    public function logout(): static
    {
        $this->user = null;

        foreach ($this->authenticators as $authenticator) {
            $authenticator->logout();
        }

        return $this;
    }

    /**
     * Get the current user.
     *
     * @return Entity|null The current user.
     */
    public function user(): Entity|null
    {
        return $this->user;
    }
}
