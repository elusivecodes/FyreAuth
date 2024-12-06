<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\Auth\Exceptions\AuthException;
use Fyre\Config\Config;
use Fyre\Container\Container;
use Fyre\Entity\Entity;
use Fyre\Http\Uri;
use Fyre\Router\Router;

use function array_filter;
use function array_key_exists;
use function get_class;
use function is_numeric;
use function is_subclass_of;

/**
 * Auth
 */
class Auth
{
    protected Access $access;

    protected array $authenticators = [];

    protected Container $container;

    protected Identifier $identifier;

    protected string $loginRoute;

    protected Router $router;

    protected Entity|null $user = null;

    /**
     * New Auth constructor.
     *
     * @param Container $container The Container.
     * @param Router $router The Router.
     * @param Config $config The Config.
     */
    public function __construct(Container $container, Router $router, Config $config)
    {
        $this->container = $container;
        $this->router = $router;

        $this->loginRoute = $config->get('Auth.loginRoute', 'login');

        $authenticators = $config->get('Auth.authenticators', []);

        foreach ($authenticators as $key => $options) {
            if (!array_key_exists('className', $options) || !is_subclass_of($options['className'], Authenticator::class)) {
                throw AuthException::forInvalidAuthenticatorClass($options['className'] ?? '');
            }

            if (is_numeric($key)) {
                $key = null;
            }

            $authenticator = $container->build($options['className'], [
                'auth' => $this,
                'options' => $options,
            ]);

            $this->addAuthenticator($authenticator, $key);
        }
    }

    /**
     * Get the Access.
     *
     * @return Access The Access.
     */
    public function access(): Access
    {
        return $this->access ??= $this->container->build(Access::class, [
            'userResolver' => fn(): Entity|null => $this->user(),
        ]);
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
        $user = $this->identifier()->attempt($identifier, $password);

        if (!$user) {
            return null;
        }

        $this->login($user, $rememberMe);

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
     * Get the login URL.
     *
     * @param string|Uri|null $redirect The redirect URI.
     * @return string The login URL.
     */
    public function getLoginUrl(string|Uri|null $redirect = null): string
    {
        return $this->router->url($this->loginRoute, [
            '?' => array_filter([
                'url' => (string) $redirect,
            ]),
        ]);
    }

    /**
     * Get the Identifier.
     *
     * @return Identifier The Identifier.
     */
    public function identifier(): Identifier
    {
        return $this->identifier ??= $this->container->build(Identifier::class);
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
