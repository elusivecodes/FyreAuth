<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\Entity\Entity;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

use function array_replace_recursive;

/**
 * Authenticator
 */
abstract class Authenticator
{
    protected static array $defaults = [];

    protected Auth $auth;

    protected array $config;

    /**
     * New Authenticator constructor.
     *
     * @param Auth $auth The Auth.
     * @param array $options Options for the handler.
     */
    public function __construct(Auth $auth, array $options = [])
    {
        $this->auth = $auth;
        $this->config = array_replace_recursive(static::$defaults, $options);
    }

    /**
     * Authenticate a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @return Entity|null The authenticated user.
     */
    public function authenticate(ServerRequest $request): Entity|null
    {
        return null;
    }

    /**
     * Update the ClientResponse before sending to client.
     *
     * @param ClientResponse $response The ClientResponse.
     * @param Entity|null The authenticated user.
     * @return ClientResponse The ClientResponse.
     */
    public function beforeResponse(ClientResponse $response, Entity|null $user = null): ClientResponse
    {
        return $response;
    }

    /**
     * Login as a user.
     *
     * @param Entity $user The user.
     * @param bool $rememberMe Whether to remember the user.
     * @return static The Auth.
     */
    public function login(Entity $user, bool $rememberMe = false): void {}

    /**
     * Logout the current user.
     *
     * @return static The Auth.
     */
    public function logout(): void {}
}
